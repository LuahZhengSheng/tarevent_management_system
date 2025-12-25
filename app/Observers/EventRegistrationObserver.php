<?php

namespace App\Observers;

use App\Models\EventRegistration;
use App\Services\NotificationService;
use App\Jobs\ProcessPaymentSuccess;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationConfirmedMail;
use App\Mail\RegistrationExpiredMail;

class EventRegistrationObserver {

    protected $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the EventRegistration "created" event.
     * 用户刚报名（pending_payment）时触发
     */
    public function created(EventRegistration $registration) {
        Log::info('Event registration created', [
            'id' => $registration->id,
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
            'status' => $registration->status,
        ]);

        // Auto-subscribe user to event notifications
        $this->notificationService->subscribeToEvent(
                $registration->user_id,
                $registration->event_id
        );

        // 站内通知：报名成功（仅 Toast，不发邮件）
        // 这个在 Controller 里用 Flash Message 处理即可
        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');
    }

    /**
     * Handle the EventRegistration "updated" event.
     */
    public function updated(EventRegistration $registration) {
        // 检测状态变化
        if ($registration->wasChanged('status')) {
            $oldStatus = $registration->getOriginal('status');
            $newStatus = $registration->status;

            Log::info('Registration status changed', [
                'id' => $registration->id,
                'old' => $oldStatus,
                'new' => $newStatus,
            ]);

            // 1️⃣ pending_payment → confirmed (付款成功)
            if ($oldStatus === 'pending_payment' && $newStatus === 'confirmed') {
                $this->handlePaymentSuccess($registration);
            }

            // 2️⃣ pending_payment → cancelled (订单取消)
            if ($oldStatus === 'pending_payment' && $newStatus === 'cancelled') {
                // 如果是系统自动取消（超时），需发邮件
                if ($registration->is_expired && !$registration->expiry_notified) {
                    $this->handlePaymentExpiry($registration);
                }
                // 如果是用户主动取消，不发邮件（Controller 已处理 Flash）
                // Unsubscribe from notifications
                $this->notificationService->unsubscribeFromEvent(
                        $registration->user_id,
                        $registration->event_id,
                        'user_cancelled'
                );
            }

            // 3️⃣ confirmed → cancelled (退款/取消)
            if ($oldStatus === 'confirmed' && $newStatus === 'cancelled') {
                $this->notificationService->unsubscribeFromEvent(
                        $registration->user_id,
                        $registration->event_id,
                        'registration_cancelled'
                );
            }

            if ($oldStatus === 'confirmed' && $newStatus === 'cancelled') {
                $this->handleRegistrationCancellation($registration);
            }
        }

        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');
    }

    /**
     * Handle the EventRegistration "deleted" event.
     */
    public function deleted(EventRegistration $registration) {
        Log::warning('Event registration deleted', [
            'id' => $registration->id,
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
        ]);

        // Unsubscribe from notifications
        $this->notificationService->unsubscribeFromEvent(
                $registration->user_id,
                $registration->event_id,
                'registration_deleted'
        );

        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');
    }

    /**
     * 处理付款成功逻辑
     */
    protected function handlePaymentSuccess(EventRegistration $registration) {
        try {
            $this->notificationService->sendPaymentSuccess($registration);
//            ProcessPaymentSuccess::dispatch($registration);

            Log::info('Payment success job dispatched to queue', [
                'registration_id' => $registration->id,
            ]);

            Log::info('Payment success notifications sent', [
                'registration_id' => $registration->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment success notifications', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 处理订单超时逻辑
     */
    protected function handlePaymentExpiry(EventRegistration $registration) {
        try {
            $this->notificationService->sendPaymentExpired($registration);

            // 标记为已通知，避免重复
            $registration->update(['expiry_notified' => true]);

            Log::info('Payment expiry notifications sent', [
                'registration_id' => $registration->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment expiry notifications', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 处理取消注册时的退款逻辑
     */
    protected function handleRegistrationCancellation(EventRegistration $registration) {
        // 只处理从 confirmed 到 cancelled 的转换
        if (!$registration->payment || $registration->payment->status !== 'success') {
            return;
        }

        // 检查是否符合退款条件
        if (!$registration->is_refund_eligible) {
            Log::info('Registration cancelled but not eligible for refund', [
                'registration_id' => $registration->id,
                'cancelled_at' => $registration->cancelled_at,
                'event_start' => $registration->event->start_time,
            ]);
            return;
        }

        // 不自动发起退款，等待用户手动申请
        Log::info('Registration cancelled and eligible for refund', [
            'registration_id' => $registration->id,
            'payment_id' => $registration->payment->id,
        ]);
    }
}
