<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller {

    /**
     * Handle Stripe Webhooks
     */
    public function handle(Request $request) {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            // 验证 webhook 签名，确保请求来自 Stripe
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: Invalid payload', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook: Invalid signature', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 根据事件类型分发（现在改成 PaymentIntent 相关事件）
        switch ($event->type) {
            case 'payment_intent.succeeded':
                // 内嵌表单支付成功
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                // 内嵌表单支付失败
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'charge.refunded':
                // Stripe 退款成功
                $this->handleChargeRefunded($event->data->object);
                break;

            case 'refund.updated':
                // Stripe 退款状态更新
                $this->handleRefundUpdated($event->data->object);
                break;

            default:
                Log::info('Stripe webhook: Unhandled event type', [
                    'type' => $event->type,
                ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment_intent.succeeded
     *
     *  - 找到 registration
     *  - 检查 status（confirmed / cancelled / expired）
     *  - 更新 Payment 为 success
     *  - 更新 registration 为 confirmed（Observer 负责发邮件/通知）
     */

    /**
     * Handle payment_intent.succeeded
     */
    protected function handlePaymentIntentSucceeded($intent) {
        $paymentIntentId = $intent->id;
        $metadata = $intent->metadata ?? null;
        $registrationId = $metadata->registration_id ?? null;

        if (!$registrationId) {
            Log::error('Stripe webhook: Missing registration_id in metadata', ['payment_intent_id' => $paymentIntentId]);
            return;
        }

        try {
            // 【关键修改】开启数据库事务
            // 使用 DB::transaction 确保整个过程是原子性的
            \Illuminate\Support\Facades\DB::transaction(function () use ($registrationId, $paymentIntentId, $intent) {

                // 【关键修改】lockForUpdate()
                // 这行代码会锁住该 Registration 记录。
                // 如果 Webhook 2 (重试请求) 同时进来，它必须在这里等待 Webhook 1 结束。
                $registration = EventRegistration::where('id', $registrationId)
                        ->lockForUpdate()
                        ->first();

                if (!$registration) {
                    // Log 在 transaction 内部可能无法立即写入文件，但在 catch 外面会捕获
                    throw new \Exception("Registration not found ID: {$registrationId}");
                }

                // 【关键修改】在锁内检查状态
                // 当 Webhook 2 终于排队结束拿到锁时，它会在这里发现状态已经是 confirmed 了，于是直接返回。
                if ($registration->status === 'confirmed') {
                    Log::info('Stripe webhook: Registration already confirmed (Idempotency Check)', [
                        'registration_id' => $registrationId,
                        'payment_intent_id' => $paymentIntentId,
                    ]);
                    return; // 退出，不执行后续逻辑
                }

                if ($registration->status === 'cancelled') {
                    Log::info('Stripe webhook: Registration cancelled, skipping', ['registration_id' => $registrationId]);
                    return;
                }

                // --- 业务逻辑开始 (只有第一个拿到锁的请求会执行到这里) ---
                // 1. 更新 Payment 表
                $payment = Payment::where('event_registration_id', $registration->id)
                        ->where('payment_intent_id', $paymentIntentId)
                        ->where('method', 'stripe')
                        ->first();

                if ($payment) {
                    $meta = $payment->metadata ?? [];
                    $meta['stripe_status'] = $intent->status;
                    $meta['stripe_payment_intent'] = $paymentIntentId;

                    // 获取卡片详情
                    if (isset($intent->charges->data[0])) {
                        $charge = $intent->charges->data[0];
                        $card = $charge->payment_method_details->card ?? null;

                        if ($card) {
                            $meta['card_brand'] = $card->brand ?? null;
                            $meta['card_last4'] = $card->last4 ?? null;
                            $meta['card_exp_month'] = $card->exp_month ?? null;
                            $meta['card_exp_year'] = $card->exp_year ?? null;
                        }
                    }

                    $payment->markAsSuccessful($meta);
                } else {
                    Log::warning('Stripe webhook: Payment not found for PaymentIntent', [
                        'registration_id' => $registrationId,
                        'payment_intent_id' => $paymentIntentId,
                    ]);
                }

                // 2. 更新 Registration 状态
                // 这会触发 Observer 发送邮件。
                // 因为我们在 Transaction 里，即使 Observer 慢，锁依然存在，别的请求进不来。
                $registration->update([
                    'status' => 'confirmed',
                ]);

                Log::info('Stripe webhook: Payment confirmed via PaymentIntent', [
                    'registration_id' => $registrationId,
                    'payment_intent_id' => $paymentIntentId,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error handling payment_intent.succeeded', [
                'payment_intent_id' => $intent->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // 可以选择抛出异常让 Stripe 稍后重试（如果是因为数据库连接错误等临时问题）
            // throw $e; 
        }
    }

    /**
     * Handle payment_intent.payment_failed
     *
     * 这里：
     *  - 标记 Payment 为 failed
     *  - 不直接取消 registration（用户重试）
     */
    protected function handlePaymentIntentFailed($intent) {
        try {
            $paymentIntentId = $intent->id;

            $payment = Payment::where('payment_intent_id', $paymentIntentId)
                    ->where('method', 'stripe')
                    ->first();

            if ($payment) {
                $message = $intent->last_payment_error->message ?? 'Payment failed.';

                $payment->markAsFailed($message);

                Log::info('Stripe webhook: PaymentIntent failed, payment marked as failed', [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);
            } else {
                Log::warning('Stripe webhook: Payment not found for failed PaymentIntent', [
                    'payment_intent_id' => $paymentIntentId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error handling payment_intent.payment_failed', [
                'payment_intent_id' => $intent->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle charge.refunded event
     */
    protected function handleChargeRefunded($charge) {
        try {
            $paymentIntentId = $charge->payment_intent ?? null;

            if (!$paymentIntentId) {
                Log::warning('Stripe webhook: No payment_intent in charge.refunded', [
                    'charge_id' => $charge->id,
                ]);
                return;
            }

            // Find payment by payment_intent_id
            $payment = Payment::where('payment_intent_id', $paymentIntentId)
                    ->where('method', 'stripe')
                    ->first();

            if (!$payment) {
                Log::warning('Stripe webhook: Payment not found for charge.refunded', [
                    'payment_intent_id' => $paymentIntentId,
                    'charge_id' => $charge->id,
                ]);
                return;
            }

            // Check if refund already completed (idempotency)
            if ($payment->refund_status === 'completed') {
                Log::info('Stripe webhook: Refund already completed', [
                    'payment_id' => $payment->id,
                    'charge_id' => $charge->id,
                ]);
                return;
            }

            // Get refund details
            $refunds = $charge->refunds->data ?? [];

            if (empty($refunds)) {
                Log::warning('Stripe webhook: No refunds in charge.refunded', [
                    'charge_id' => $charge->id,
                ]);
                return;
            }

            $refund = $refunds[0]; // Get the latest refund
            // Update payment
            $metadata = [
                'stripe_refund_id' => $refund->id,
                'stripe_charge_id' => $charge->id,
                'refund_status' => $refund->status,
                'refund_reason' => $refund->reason ?? null,
            ];

            $payment->completeRefund($refund->id, $metadata);

            Log::info('Stripe webhook: Refund completed via charge.refunded', [
                'payment_id' => $payment->id,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error handling charge.refunded', [
                'charge_id' => $charge->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle refund.updated event
     */
    protected function handleRefundUpdated($refund) {
        try {
            $refundId = $refund->id;

            // Find payment by refund_transaction_id or metadata
            $payment = Payment::where('refund_transaction_id', $refundId)
                    ->where('method', 'stripe')
                    ->first();

            if (!$payment) {
                // Try to find by metadata
                $payment = Payment::where('method', 'stripe')
                        ->where('refund_metadata->stripe_refund_id', $refundId)
                        ->first();
            }

            if (!$payment) {
                Log::warning('Stripe webhook: Payment not found for refund.updated', [
                    'refund_id' => $refundId,
                ]);
                return;
            }

            // Handle based on refund status
            if ($refund->status === 'succeeded' && $payment->refund_status !== 'completed') {
                $metadata = [
                    'stripe_refund_id' => $refund->id,
                    'refund_status' => $refund->status,
                ];

                $payment->completeRefund($refund->id, $metadata);

                Log::info('Stripe webhook: Refund succeeded via refund.updated', [
                    'payment_id' => $payment->id,
                    'refund_id' => $refund->id,
                ]);
            } elseif ($refund->status === 'failed') {
                $payment->update([
                    'refund_status' => 'failed',
                    'refund_rejection_reason' => $refund->failure_reason ?? 'Refund failed',
                ]);

                Log::warning('Stripe webhook: Refund failed', [
                    'payment_id' => $payment->id,
                    'refund_id' => $refund->id,
                    'reason' => $refund->failure_reason,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error handling refund.updated', [
                'refund_id' => $refund->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
