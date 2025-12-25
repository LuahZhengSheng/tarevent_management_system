<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventSubscription;
use App\Models\User;
use App\Models\Notification;
use App\Models\Payment;
use App\Mail\EventUpdatedMail;
use App\Mail\EventCancelledMail;
use App\Mail\EventTimeChangedMail;
use App\Mail\EventVenueChangedMail;
use App\Mail\RegistrationConfirmedMail;
use App\Mail\RegistrationExpiredMail;
use App\Mail\RefundCompletedMail;
use App\Mail\RefundRejectedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService {

    /**
     * Critical fields that require EMAIL notification
     */
    const EMAIL_NOTIFICATION_FIELDS = [
        'start_time',
        'end_time',
        'venue',
        'status', // For cancelled events
        'registration_end_time', // Only if shortened
    ];

    /**
     * Fields that require IN-APP notification
     */
    const INAPP_NOTIFICATION_FIELDS = [
        'title',
        'description',
        'category',
        'poster_path',
        'fee_amount',
        'max_participants',
        'contact_email',
        'contact_phone',
        'registration_instructions',
        'requirements',
    ];

    /**
     * Handle event updates and send appropriate notifications
     */
    public function handleEventUpdate(Event $event, array $changes) {
        // Don't notify if event is past or cancelled
        if ($event->end_time < now() || $event->status === 'cancelled') {
            Log::info('Skipping notifications for past/cancelled event', [
                'event_id' => $event->id,
                'status' => $event->status,
            ]);
            return;
        }

        // Get all active subscribers
        $subscribers = $this->getActiveSubscribers($event);

        if ($subscribers->isEmpty()) {
            return;
        }

        // Determine notification type and priority
        $needsEmail = $this->needsEmailNotification($changes);
        $needsInApp = $this->needsInAppNotification($changes);

        if (!$needsEmail && !$needsInApp) {
            return;
        }

        // Process each type of change
        if (isset($changes['start_time']) || isset($changes['end_time'])) {
            $this->notifyTimeChange($event, $subscribers, $changes);
        }

        if (isset($changes['venue'])) {
            $this->notifyVenueChange($event, $subscribers, $changes);
        }

        if (isset($changes['registration_end_time'])) {
            $this->notifyRegistrationTimeChange($event, $subscribers, $changes);
        }

        // General update notification for other changes
        if ($needsInApp && !isset($changes['start_time']) && !isset($changes['end_time']) && !isset($changes['venue'])) {
            $this->notifyGeneralUpdate($event, $subscribers, $changes);
        }
    }

    /**
     * Notify subscribers about time changes (EMAIL + IN-APP)
     */
    protected function notifyTimeChange(Event $event, $subscribers, array $changes) {
        foreach ($subscribers as $subscriber) {
            // Create in-app notification
            Notification::create([
                'user_id' => $subscriber->id,
                'type' => 'event_time_changed',
                'title' => 'Event Time Changed',
                'message' => "The schedule for '{$event->title}' has been updated. Please check the new timing.",
                'data' => json_encode([
                    'event_id' => $event->id,
                    'changes' => $changes,
                    'old_start_time' => $changes['start_time']['old'] ?? null,
                    'new_start_time' => $changes['start_time']['new'] ?? null,
                    'old_end_time' => $changes['end_time']['old'] ?? null,
                    'new_end_time' => $changes['end_time']['new'] ?? null,
                ]),
                'channel' => 'both',
                'priority' => 'high',
                'sent_at' => now(),
            ]);

            // Send email notification
            try {
                Mail::to($subscriber->email)->send(
                        new EventTimeChangedMail($event, $subscriber, $changes)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send time change email', [
                    'user_id' => $subscriber->id,
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Time change notifications sent', [
            'event_id' => $event->id,
            'subscribers_count' => $subscribers->count(),
        ]);
    }

    /**
     * Notify subscribers about venue changes (EMAIL + IN-APP)
     */
    protected function notifyVenueChange(Event $event, $subscribers, array $changes) {
        foreach ($subscribers as $subscriber) {
            // Create in-app notification
            Notification::create([
                'user_id' => $subscriber->id,
                'type' => 'event_venue_changed',
                'title' => 'Event Venue Changed',
                'message' => "The venue for '{$event->title}' has been changed. Please note the new location.",
                'data' => json_encode([
                    'event_id' => $event->id,
                    'changes' => $changes,
                    'old_venue' => $changes['venue']['old'] ?? null,
                    'new_venue' => $changes['venue']['new'] ?? null,
                ]),
                'channel' => 'both',
                'priority' => 'high',
                'sent_at' => now(),
            ]);

            // Send email notification
            try {
                Mail::to($subscriber->email)->send(
                        new EventVenueChangedMail($event, $subscriber, $changes)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send venue change email', [
                    'user_id' => $subscriber->id,
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Venue change notifications sent', [
            'event_id' => $event->id,
            'subscribers_count' => $subscribers->count(),
        ]);
    }

    /**
     * Notify about registration time changes (EMAIL if shortened)
     */
    protected function notifyRegistrationTimeChange(Event $event, $subscribers, array $changes) {
        $oldTime = strtotime($changes['registration_end_time']['old']);
        $newTime = strtotime($changes['registration_end_time']['new']);

        // Only notify if registration time was shortened
        if ($newTime < $oldTime) {
            foreach ($subscribers as $subscriber) {
                Notification::create([
                    'user_id' => $subscriber->id,
                    'type' => 'event_updated',
                    'title' => 'Registration Deadline Changed',
                    'message' => "The registration deadline for '{$event->title}' has been moved earlier. Please register soon!",
                    'data' => json_encode([
                        'event_id' => $event->id,
                        'changes' => $changes,
                    ]),
                    'channel' => 'both',
                    'priority' => 'high',
                    'sent_at' => now(),
                ]);

                // Send email
                try {
                    Mail::to($subscriber->email)->send(
                            new EventUpdatedMail($event, $subscriber, $changes)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send registration time change email', [
                        'user_id' => $subscriber->id,
                        'event_id' => $event->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Notify about general updates (IN-APP only)
     */
    protected function notifyGeneralUpdate(Event $event, $subscribers, array $changes) {
        $changedFields = array_keys($changes);
        $changedFieldsText = implode(', ', $changedFields);

        foreach ($subscribers as $subscriber) {
            Notification::create([
                'user_id' => $subscriber->id,
                'type' => 'event_updated',
                'title' => 'Event Details Updated',
                'message' => "Some details of '{$event->title}' have been updated. Check it out!",
                'data' => json_encode([
                    'event_id' => $event->id,
                    'changes' => $changes,
                    'changed_fields' => $changedFields,
                ]),
                'channel' => 'database',
                'priority' => 'normal',
                'sent_at' => now(),
            ]);
        }

        Log::info('General update notifications sent', [
            'event_id' => $event->id,
            'subscribers_count' => $subscribers->count(),
            'changed_fields' => $changedFieldsText,
        ]);
    }

    /**
     * Notify about event cancellation (EMAIL + IN-APP)
     */
    public function notifyEventCancellation(Event $event) {
        $subscribers = $this->getActiveSubscribers($event);

        foreach ($subscribers as $subscriber) {
            Notification::create([
                'user_id' => $subscriber->id,
                'type' => 'event_cancelled',
                'title' => 'Event Cancelled',
                'message' => "The event '{$event->title}' has been cancelled. {$event->cancelled_reason}",
                'data' => json_encode([
                    'event_id' => $event->id,
                    'cancelled_reason' => $event->cancelled_reason,
                ]),
                'channel' => 'both',
                'priority' => 'urgent',
                'sent_at' => now(),
            ]);

            // Send email
            try {
                Mail::to($subscriber->email)->send(
                        new EventCancelledMail($event, $subscriber)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation email', [
                    'user_id' => $subscriber->id,
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Unsubscribe all users from this cancelled event
        EventSubscription::where('event_id', $event->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'unsubscribed_at' => now(),
                    'reason' => 'event_cancelled',
        ]);

        Log::info('Event cancellation notifications sent', [
            'event_id' => $event->id,
            'subscribers_count' => $subscribers->count(),
        ]);
    }

    /**
     * Subscribe user to event notifications
     */
    public function subscribeToEvent($userId, $eventId) {
        return EventSubscription::subscribe($userId, $eventId);
    }

    /**
     * Unsubscribe user from event notifications
     */
    public function unsubscribeFromEvent($userId, $eventId, $reason = null) {
        EventSubscription::unsubscribeFromEvent($userId, $eventId, $reason);
    }

    /**
     * Auto-unsubscribe users from past events
     */
    public function unsubscribeFromPastEvents() {
        $pastEvents = Event::where('end_time', '<', now())->pluck('id');

        $unsubscribed = EventSubscription::whereIn('event_id', $pastEvents)
                ->where('is_active', true)
                ->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
            'reason' => 'event_ended',
        ]);

        Log::info('Auto-unsubscribed from past events', [
            'count' => $unsubscribed,
        ]);

        return $unsubscribed;
    }

    public function sendNotification(
            int $userId,
            string $type,
            string $title,
            string $message,
            array $data = [],
            string $channel = 'database', // database / mail / both
            string $priority = 'normal'
    ) {
        // 1. 用模型的工厂方法创建站内通知
        $notification = Notification::sendToUser(
                        $userId,
                        $type,
                        $title,
                        $message,
                        $data,
                        $channel,
                        $priority
        );

        // 2. 如需顺便发一封简单邮件（可选）
        if ($channel === 'mail' || $channel === 'both') {
            try {
                $user = User::find($userId);
                if ($user && $user->email) {
                    Mail::raw($message, function ($mail) use ($user, $title) {
                        $mail->to($user->email)->subject($title);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Failed to send generic notification email', [
                    'user_id' => $userId,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    public function sendPaymentSuccess(EventRegistration $registration) {
        // 邮件
        try {
            Mail::to($registration->email)
                    ->send(new RegistrationConfirmedMail($registration));
        } catch (\Exception $e) {
            Log::error('Failed to send payment success email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 站内通知
        $this->sendNotification(
                $registration->user_id,
                'payment_success',
                'Payment Successful! 🎉',
                "Your registration for '{$registration->event->title}' is confirmed. Check your email for details.",
                [
                    'event_id' => $registration->event_id,
                    'registration_id' => $registration->id,
                    'payment_id' => $registration->payment->id ?? null,
                ],
                'database',
                'high'
        );
    }

    public function sendPaymentExpired(EventRegistration $registration) {
        // 邮件：用做好的模板
        try {
            Mail::to($registration->email)
                    ->send(new RegistrationExpiredMail($registration));
        } catch (\Exception $e) {
            Log::error('Failed to send payment expired email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 站内通知
        $this->sendNotification(
                $registration->user_id,
                'payment_expired',
                'Payment Time Expired ⏰',
                "Your registration for '{$registration->event->title}' has expired. Please register again if spots are still available.",
                [
                    'event_id' => $registration->event_id,
                    'registration_id' => $registration->id,
                ],
                'database',
                'high'
        );
    }

    /**
     * Refund request approved (进入 processing/待网关完成)
     * 这里只做站内通知，邮件可选
     */
    public function sendRefundApproved(Payment $payment) {
        $registration = $payment->registration;

        // 站内通知
        $this->sendNotification(
                $payment->user_id,
                'refund_approved',
                'Refund Request Approved',
                "Your refund request for '{$registration->event->title}' has been approved and is being processed.",
                [
                    'event_id' => $registration->event_id,
                    'registration_id' => $registration->id,
                    'payment_id' => $payment->id,
                    'refund_amount' => $payment->refund_amount,
                ],
                'database',
                'high'
        );
    }

    /**
     * Refund completed: 网关已确认退款到账
     */
    public function sendRefundCompleted(Payment $payment) {
        $registration = $payment->registration;

        // 站内通知
        $this->sendNotification(
                $payment->user_id,
                'refund_completed',
                'Refund Completed',
                "Your refund of RM " . number_format($payment->refund_amount, 2) .
                " for '{$registration->event->title}' has been processed successfully.",
                [
                    'event_id' => $registration->event_id,
                    'registration_id' => $registration->id,
                    'payment_id' => $payment->id,
                    'refund_amount' => $payment->refund_amount,
                ],
                'database',
                'high'
        );

        // 邮件
        try {
            Mail::to($payment->user->email)
                    ->send(new RefundCompletedMail($payment));
        } catch (\Exception $e) {
            Log::error('Failed to send refund completed email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 完成退款后自动 unsubscribe 这个活动
        $this->unsubscribeFromEvent(
                $payment->user_id,
                $registration->event_id,
                'refund_completed'
        );
    }

    /**
     * Refund rejected: 审核拒绝
     */
    public function sendRefundRejected(Payment $payment) {
        $registration = $payment->registration;

        // 站内通知
        $this->sendNotification(
                $payment->user_id,
                'refund_rejected',
                'Refund Request Rejected',
                "Your refund request for '{$registration->event->title}' has been rejected. " .
                "Reason: {$payment->refund_rejection_reason}",
                [
                    'event_id' => $registration->event_id,
                    'registration_id' => $registration->id,
                    'payment_id' => $payment->id,
                    'reason' => $payment->refund_rejection_reason,
                ],
                'database',
                'high'
        );

        // 邮件
        try {
            Mail::to($payment->user->email)
                    ->send(new RefundRejectedMail($payment));
        } catch (\Exception $e) {
            Log::error('Failed to send refund rejected email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get active subscribers for an event
     */
    protected function getActiveSubscribers(Event $event) {
        return User::whereHas('eventSubscriptions', function ($query) use ($event) {
                    $query->where('event_id', $event->id)
                            ->where('is_active', true);
                })->get();
    }

    /**
     * Check if changes require email notification
     */
    protected function needsEmailNotification(array $changes) {
        foreach (self::EMAIL_NOTIFICATION_FIELDS as $field) {
            if (isset($changes[$field])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if changes require in-app notification
     */
    protected function needsInAppNotification(array $changes) {
        foreach (self::INAPP_NOTIFICATION_FIELDS as $field) {
            if (isset($changes[$field])) {
                return true;
            }
        }
        return false;
    }
}
