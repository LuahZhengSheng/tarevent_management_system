<?php

//

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PayPalWebhookController extends Controller {

    /**
     * Handle PayPal Webhooks
     */
    public function handle(Request $request) {
        // 1. 获取所有 PayPal 发来的头信息
        $headers = array_change_key_case($request->headers->all(), CASE_UPPER);
        $transmissionId = $headers['PAYPAL-TRANSMISSION-ID'][0] ?? null;
        $transmissionTime = $headers['PAYPAL-TRANSMISSION-TIME'][0] ?? null;
        $certUrl = $headers['PAYPAL-CERT-URL'][0] ?? null;
        $authAlgo = $headers['PAYPAL-AUTH-ALGO'][0] ?? null;
        $transmissionSig = $headers['PAYPAL-TRANSMISSION-SIG'][0] ?? null;

        $webhookId = config('services.paypal.webhook_id');
        $payload = $request->all();

        // 2. 验证签名 (Verify Signature)
        try {
            $isSignatureValid = $this->verifySignature(
                    $transmissionId,
                    $transmissionTime,
                    $certUrl,
                    $authAlgo,
                    $transmissionSig,
                    $webhookId,
                    $payload
            );

            if (!$isSignatureValid) {
                Log::error('PayPal Webhook: Invalid Signature');
                return response()->json(['error' => 'Invalid Signature'], 400);
            }
        } catch (\Exception $e) {
            Log::error('PayPal Webhook: Verification Error', ['message' => $e->getMessage()]);
            // 开发环境下如果没配好 ID 可能会报错，先 return 400
            return response()->json(['error' => 'Verification Error'], 400);
        }

        // 3. 处理事件
        $eventType = $payload['event_type'] ?? null;

        Log::info('PayPal Webhook Verified', ['type' => $eventType]);

        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                // 支付成功
                $this->handleCaptureCompleted($payload);
                break;

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.REVERSED':
                // 支付被拒绝 / 退款 / 冲销 等，都视为失败
                $this->handleCaptureFailed($payload);
                break;

            case 'PAYMENT.CAPTURE.REFUNDED':
                // PayPal 退款成功
                $this->handleCaptureRefunded($payload);
                break;

            default:
                // 其他事件先只打日志
                Log::info('PayPal Webhook: Unhandled event type', [
                    'type' => $eventType,
                ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Verify PayPal Webhook Signature via API
     */
    private function verifySignature($transmissionId, $transmissionTime, $certUrl, $authAlgo, $transmissionSig, $webhookId, $event) {
        // 获取 Access Token
        $accessToken = $this->getAccessToken();

        $baseUrl = config('services.paypal.mode') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // 构建验证请求体
        $verifyPayload = [
            'auth_algo' => $authAlgo,
            'cert_url' => $certUrl,
            'transmission_id' => $transmissionId,
            'transmission_sig' => $transmissionSig,
            'transmission_time' => $transmissionTime,
            'webhook_id' => $webhookId,
            'webhook_event' => $event
        ];

        // 发送给 PayPal 验证
        $response = Http::withToken($accessToken)
                ->post("{$baseUrl}/v1/notifications/verify-webhook-signature", $verifyPayload);

        if ($response->failed()) {
            Log::error('PayPal Verify API Failed', ['response' => $response->body()]);
            return false;
        }

        $result = $response->json();
        return isset($result['verification_status']) && $result['verification_status'] === 'SUCCESS';
    }

    /**
     * Helper: Get PayPal Access Token
     */
    private function getAccessToken() {
        // 可以把这个方法提取到一个 Service 里，或者用 Cache 缓存 Token，不要每次都请求
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        $baseUrl = config('services.paypal.mode') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $response = Http::withBasicAuth($clientId, $secret)
                ->asForm()
                ->post("{$baseUrl}/v1/oauth2/token", [
            'grant_type' => 'client_credentials'
        ]);

        return $response->json()['access_token'];
    }

    /**
     * 处理扣款成功事件
     */
    protected function handleCaptureCompleted($payload) {
        $resource = $payload['resource'];

        // 从 payload 中提取关键 ID
        // PayPal 的 payload 结构很深，通常 resource.supplementary_data.related_ids.order_id 是我们在 createOrder 时拿到的 ID
        // 或者直接匹配 resource.id (这是 capture ID)，但这需要在 payment 表里存 capture_id
        // 最稳妥的方法：我们在 createOrder 时，通常会把 registration_id 塞进 custom_id
        // 如果没塞，我们只能靠 order_id 来反查

        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        if (!$orderId) {
            Log::error('PayPal Webhook: Could not find order_id in payload', $payload);
            return;
        }

        // 查找对应的 Payment 记录
        $payment = Payment::where('transaction_id', $orderId)
                ->where('method', 'paypal')
                ->first();

        if ($payment && $payment->status !== 'success') {
            // 更新数据库状态
            $metadata = $payment->metadata ?? [];
            $metadata['webhook_received'] = true;
            $metadata['capture_id'] = $resource['id'];

            $payment->markAsSuccessful($metadata);

            Log::info("PayPal Order #{$orderId} confirmed via Webhook");
        } else {
            Log::info("PayPal Webhook: Payment not found or already success for Order #{$orderId}");
        }
    }

    /**
     * Handle PAYMENT.CAPTURE.DENIED / REFUNDED / REVERSED
     *
     * 统一把相关 Payment 标记为 failed，并记录清楚原因。
     */
    protected function handleCaptureFailed(array $payload) {
        try {
            $resource = $payload['resource'] ?? [];
            $eventType = $payload['event_type'] ?? 'UNKNOWN';

            // 从 payload 中提取 order_id（你在 createPayPalOrder 里用的是 orderId 作为 transaction_id）
            $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

            if (!$orderId) {
                Log::error('PayPal Webhook: Could not find order_id in failed payload', [
                    'event_type' => $eventType,
                    'payload' => $payload,
                ]);
                return;
            }

            // 找出对应的 Payment 记录
            $payment = Payment::where('transaction_id', $orderId)
                    ->where('method', 'paypal')
                    ->first();

            if (!$payment) {
                Log::warning('PayPal Webhook: Payment not found for failed order', [
                    'event_type' => $eventType,
                    'order_id' => $orderId,
                ]);
                return;
            }

            // 如果已经是 success，可以根据业务选择忽略或标记为 refunded
            if ($payment->status === 'success') {
                Log::info('PayPal Webhook: Payment already success for failed event, skip markAsFailed', [
                    'event_type' => $eventType,
                    'order_id' => $orderId,
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            // 组装失败原因（事件类型 + PayPal 给的状态/说明）
            $status = $resource['status'] ?? 'UNKNOWN';
            $reason = $resource['reason_code'] ?? null;
            $message = "PayPal capture failed: {$eventType}, status={$status}";
            if ($reason) {
                $message .= ", reason={$reason}";
            }

            // 标记为 failed，并把原因写进 metadata / error_message
            $payment->markAsFailed($message);

            Log::info('PayPal Webhook: Payment marked as failed via webhook', [
                'event_type' => $eventType,
                'order_id' => $orderId,
                'payment_id' => $payment->id,
                'status' => $status,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal Webhook: Error handling failed capture event', [
                'event_type' => $payload['event_type'] ?? 'UNKNOWN',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle PAYMENT.CAPTURE.REFUNDED event
     */
    protected function handleCaptureRefunded(array $payload) {
        try {
            $resource = $payload['resource'] ?? [];
            $eventType = $payload['event_type'] ?? 'UNKNOWN';

            // Get Capture ID from links (rel="up") since order_id is missing in refund payload
            // PayPal Refund webhook does not provide order_id directly.
            $captureId = null;
            if (isset($resource['links']) && is_array($resource['links'])) {
                foreach ($resource['links'] as $link) {
                    if (isset($link['rel']) && $link['rel'] === 'up') {
                        // href format: .../v2/payments/captures/{CAPTURE_ID}
                        $parts = explode('/', $link['href']);
                        $captureId = end($parts);
                        break;
                    }
                }
            }

            // Also get Refund ID directly
            $refundId = $resource['id'] ?? null;

            if (!$captureId && !$refundId) {
                Log::error('PayPal Webhook: Could not extract Capture ID or Refund ID', [
                    'event_type' => $eventType,
                    'payload' => $payload,
                ]);
                return;
            }

            // Find payment by matching metadata (preferred) or other fields
            // We search by Refund ID first (most accurate), then Capture ID
            $payment = Payment::query()
                    ->where('metadata->paypal_refund_id', $refundId)
                    ->orWhere('metadata->capture_id', $captureId)
                    // If your transaction_id stores Capture ID (not Order ID), uncomment below:
                    // ->orWhere('transaction_id', $captureId) 
                    ->first();

            if (!$payment) {
                Log::warning('PayPal Webhook: Payment not found for Refund Webhook', [
                    'event_type' => $eventType,
                    'capture_id' => $captureId,
                    'refund_id' => $refundId,
                ]);
                return;
            }

            // Check if refund already completed (idempotency)
            if ($payment->refund_status === 'completed') {
                Log::info('PayPal Webhook: Refund already completed', [
                    'payment_id' => $payment->id,
                    'refund_id' => $refundId,
                ]);
                return;
            }

            // Get refund details
            $status = $resource['status'] ?? 'COMPLETED';

            // Build metadata
            $metadata = [
                'paypal_refund_id' => $refundId,
                'paypal_capture_id' => $captureId,
                'refund_status' => $status,
                'webhook_received' => true,
            ];

            // Complete refund
            // Check if method exists in Payment model, otherwise update directly
            if (method_exists($payment, 'completeRefund')) {
                $payment->completeRefund($refundId, $metadata);
            } else {
                $payment->update([
                    'refund_status' => strtolower($status),
                    'refund_processed_at' => now(),
                    'metadata' => array_merge($payment->metadata ?? [], $metadata)
                ]);
            }

            Log::info('PayPal Webhook: Refund completed', [
                'payment_id' => $payment->id,
                'capture_id' => $captureId,
                'refund_id' => $refundId,
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal Webhook: Error handling capture refunded', [
                'event_type' => $payload['event_type'] ?? 'UNKNOWN',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
