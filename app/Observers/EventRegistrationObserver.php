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
//    public function creating(EventRegistration $registration)
//    {
//        // Capture security information
//        $registration->ip_address = request()->ip();
//        $registration->user_agent = request()->userAgent();
//
//        Log::info('Event registration creating', [
//            'event_id' => $registration->event_id,
//            'user_id' => $registration->user_id,
//            'ip' => $registration->ip_address,
//        ]);
//    }

    /**
     * Handle the EventRegistration "created" event.
     */
    public function created(EventRegistration $registration)
    {
        Log::info('Event registration created', [
            'id' => $registration->id,
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
        ]);

        // Auto-subscribe user to event notifications
        $this->notificationService->subscribeToEvent(
            $registration->user_id, 
            $registration->event_id
        );

        Log::info('User auto-subscribed to event notifications', [
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
        ]);

        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');
    }

        /**
     * Handle the EventRegistration "updated" event.
     */
    public function updated(EventRegistration $registration)
    {
        // If registration is cancelled, unsubscribe from notifications
        if ($registration->wasChanged('status') && $registration->status === 'cancelled') {
            $this->notificationService->unsubscribeFromEvent(
                $registration->user_id,
                $registration->event_id,
                'user_cancelled'
            );

            Log::info('User unsubscribed from event notifications after cancellation', [
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
            ]);
        }

        // Clear cache
        Cache::forget('event_' . $registration->event_id . '_registrations');
        Cache::forget('user_' . $registration->user_id . '_registrations');
    }

    /**
     * Handle the EventRegistration "deleted" event.
     */
    public function deleted(EventRegistration $registration)
    {
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
}