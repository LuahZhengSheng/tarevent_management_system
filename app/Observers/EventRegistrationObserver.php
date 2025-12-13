<?php

namespace App\Observers;

use App\Models\EventRegistration;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EventRegistrationObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the EventRegistration "creating" event.
     */
    public function creating(EventRegistration $registration)
    {
        // Capture security information
        $registration->ip_address = request()->ip();
        $registration->user_agent = request()->userAgent();

        Log::info('Event registration creating', [
            'event_id' => $registration->event_id,
            'user_id' => $registration->user_id,
            'ip' => $registration->ip_address,
        ]);
    }

    /**
     * Handle the EventRegistration "created" event.
     */
    public function created(EventRegistration $registration)
    {
        Log::info('Event registration created', [
            'id' => $registration->id,
            'registration_number' => $registration->registration_number,
            'event_id' => $registration->event_id,
            'user_id' => $registration->user_id,
            'status' => $registration->status,
        ]);

        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');

        // Send confirmation notification to user
        $this->notificationService->sendRegistrationConfirmation($registration);

        // Notify event organizer about new registration
        $this->notificationService->notifyOrganizerAboutNewRegistration($registration);

        // If event is now full, notify waitlisted users
        if ($registration->event->is_full) {
            $this->notificationService->notifyEventIsFull($registration->event);
        }
    }

    /**
     * Handle the EventRegistration "updating" event.
     */
    public function updating(EventRegistration $registration)
    {
        // Track status changes
        if ($registration->isDirty('status')) {
            $oldStatus = $registration->getOriginal('status');
            $newStatus = $registration->status;

            Log::info('Registration status changing', [
                'id' => $registration->id,
                'from' => $oldStatus,
                'to' => $newStatus,
            ]);

            // If changing to cancelled, set cancelled_at
            if ($newStatus === 'cancelled' && !$registration->cancelled_at) {
                $registration->cancelled_at = now();
            }

            // If changing to confirmed from pending_payment
            if ($oldStatus === 'pending_payment' && $newStatus === 'confirmed') {
                Log::info('Payment confirmed for registration', [
                    'id' => $registration->id,
                ]);
            }
        }
    }

    /**
     * Handle the EventRegistration "updated" event.
     */
    public function updated(EventRegistration $registration)
    {
        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');

        // Handle status change notifications
        if ($registration->wasChanged('status')) {
            $oldStatus = $registration->getOriginal('status');
            $newStatus = $registration->status;

            // Payment confirmed
            if ($oldStatus === 'pending_payment' && $newStatus === 'confirmed') {
                $this->notificationService->sendPaymentConfirmation($registration);
            }

            // Registration cancelled
            if ($newStatus === 'cancelled') {
                $this->notificationService->sendCancellationConfirmation($registration);
                
                // Notify next waitlisted person if any
                $this->notificationService->notifyWaitlistedUsers($registration->event);
            }
        }

        // Check-in notification
        if ($registration->wasChanged('checked_in_at') && $registration->checked_in_at) {
            Log::info('User checked in to event', [
                'registration_id' => $registration->id,
                'event_id' => $registration->event_id,
                'user_id' => $registration->user_id,
            ]);
        }

        // Refund processed notification
        if ($registration->wasChanged('refund_status') && $registration->refund_status === 'processed') {
            $this->notificationService->sendRefundConfirmation($registration);
        }
    }

    /**
     * Handle the EventRegistration "deleted" event.
     */
    public function deleted(EventRegistration $registration)
    {
        Log::warning('Event registration soft deleted', [
            'id' => $registration->id,
            'event_id' => $registration->event_id,
            'user_id' => $registration->user_id,
        ]);

        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');
    }

    /**
     * Handle the EventRegistration "restored" event.
     */
    public function restored(EventRegistration $registration)
    {
        Log::info('Event registration restored', [
            'id' => $registration->id,
        ]);

        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');
    }
}