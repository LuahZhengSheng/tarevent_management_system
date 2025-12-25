<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // 只关心 refund_status 变化
        if ($payment->wasChanged('refund_status')) {
            $old = $payment->getOriginal('refund_status');
            $new = $payment->refund_status;

            Log::info('Payment refund_status changed', [
                'payment_id' => $payment->id,
                'old'        => $old,
                'new'        => $new,
            ]);

            // 1️⃣ pending -> processing：退款申请批准（开始处理）
            if ($old === 'pending' && $new === 'processing') {
                $this->handleRefundApproved($payment);
            }

            // 2️⃣ 任意 -> completed：退款完成
            if ($new === 'completed' && $old !== 'completed') {
                $this->handleRefundCompleted($payment);
            }

            // 3️⃣ 任意 -> rejected：退款被拒
            if ($new === 'rejected' && $old !== 'rejected') {
                $this->handleRefundRejected($payment);
            }
        }
    }

    /**
     * 处理退款批准（进入 processing）通知
     */
    protected function handleRefundApproved(Payment $payment): void
    {
        try {
            $this->notificationService->sendRefundApproved($payment);

            Log::info('Refund approved notifications sent', [
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund approved notifications', [
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * 处理退款完成通知（站内 + 邮件 + unsubscribe）
     */
    protected function handleRefundCompleted(Payment $payment): void
    {
        try {
            $this->notificationService->sendRefundCompleted($payment);

            Log::info('Refund completed notifications sent', [
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund completed notifications', [
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * 处理退款被拒通知
     */
    protected function handleRefundRejected(Payment $payment): void
    {
        try {
            $this->notificationService->sendRefundRejected($payment);

            Log::info('Refund rejected notifications sent', [
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund rejected notifications', [
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
