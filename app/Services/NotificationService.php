<?php 

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventSubscription;
use App\Models\User;
use App\Models\Notification;
use App\Mail\EventUpdatedMail;
use App\Mail\EventCancelledMail;
use App\Mail\EventTimeChangedMail;
use App\Mail\EventVenueChangedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
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
    public function handleEventUpdate(Event $event, array $changes)
    {
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
    protected function notifyTimeChange(Event $event, $subscribers, array $changes)
    {
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
    protected function notifyVenueChange(Event $event, $subscribers, array $changes)
    {
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
    protected function notifyRegistrationTimeChange(Event $event, $subscribers, array $changes)
    {
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
    protected function notifyGeneralUpdate(Event $event, $subscribers, array $changes)
    {
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
    public function notifyEventCancellation(Event $event)
    {
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
    public function subscribeToEvent($userId, $eventId)
    {
        return EventSubscription::subscribe($userId, $eventId);
    }

    /**
     * Unsubscribe user from event notifications
     */
    public function unsubscribeFromEvent($userId, $eventId, $reason = null)
    {
        EventSubscription::unsubscribeFromEvent($userId, $eventId, $reason);
    }

    /**
     * Auto-unsubscribe users from past events
     */
    public function unsubscribeFromPastEvents()
    {
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

    /**
     * Get active subscribers for an event
     */
    protected function getActiveSubscribers(Event $event)
    {
        return User::whereHas('eventSubscriptions', function ($query) use ($event) {
            $query->where('event_id', $event->id)
                  ->where('is_active', true);
        })->get();
    }

    /**
     * Check if changes require email notification
     */
    protected function needsEmailNotification(array $changes)
    {
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
    protected function needsInAppNotification(array $changes)
    {
        foreach (self::INAPP_NOTIFICATION_FIELDS as $field) {
            if (isset($changes[$field])) {
                return true;
            }
        }
        return false;
    }
}

//namespace App\Services;
//
//use App\Models\Event;
//use App\Models\EventRegistration;
//use App\Models\User;
//use App\Models\Notification;
//use Illuminate\Support\Facades\Mail;
//use Illuminate\Support\Facades\Log;
//
//class NotificationService
//{
//    /**
//     * Notify admins about new event creation
//     */
//    public function notifyAdminsAboutNewEvent(Event $event)
//    {
//        $admins = User::where('role', 'admin')->get();
//
//        foreach ($admins as $admin) {
//            Notification::create([
//                'user_id' => $admin->id,
//                'type' => 'event_created',
//                'title' => 'New Event Created',
//                'message' => "A new event '{$event->title}' has been created and requires approval.",
//                'data' => json_encode(['event_id' => $event->id]),
//                'read_at' => null,
//            ]);
//        }
//
//        Log::info('Admins notified about new event', ['event_id' => $event->id]);
//    }
//
//    /**
//     * Notify users about published event
//     */
//    public function notifyUsersAboutNewEvent(Event $event)
//    {
//        // Only notify for public events
//        if (!$event->is_public) {
//            return;
//        }
//
//        // Get users interested in this category (implement as needed)
//        $users = User::where('role', 'student')
//                     ->whereJsonContains('interested_categories', $event->category)
//                     ->get();
//
//        foreach ($users as $user) {
//            Notification::create([
//                'user_id' => $user->id,
//                'type' => 'new_event',
//                'title' => 'New Event Available',
//                'message' => "Check out the new event: {$event->title}",
//                'data' => json_encode(['event_id' => $event->id]),
//                'read_at' => null,
//            ]);
//        }
//
//        Log::info('Users notified about published event', ['event_id' => $event->id]);
//    }
//
//    /**
//     * Send registration confirmation to user
//     */
//    public function sendRegistrationConfirmation(EventRegistration $registration)
//    {
//        $event = $registration->event;
//        $user = $registration->user;
//
//        if (!$user) {
//            return;
//        }
//
//        $message = $registration->status === 'pending_payment'
//            ? "You have successfully registered for '{$event->title}'. Please complete payment to confirm your spot."
//            : "Your registration for '{$event->title}' has been confirmed! Registration number: {$registration->registration_number}";
//
//        Notification::create([
//            'user_id' => $user->id,
//            'type' => 'registration_confirmed',
//            'title' => 'Registration Received',
//            'message' => $message,
//            'data' => json_encode([
//                'event_id' => $event->id,
//                'registration_id' => $registration->id,
//                'registration_number' => $registration->registration_number,
//            ]),
//            'read_at' => null,
//        ]);
//
//        // TODO: Send email confirmation
//        // Mail::to($user->email)->send(new RegistrationConfirmationMail($registration));
//
//        Log::info('Registration confirmation sent', [
//            'registration_id' => $registration->id,
//            'user_id' => $user->id,
//        ]);
//    }
//
//    /**
//     * Notify organizer about new registration
//     */
//    public function notifyOrganizerAboutNewRegistration(EventRegistration $registration)
//    {
//        $event = $registration->event;
//        
//        // TODO: Get club admins/organizers
//        // For now, just log it
//        Log::info('New registration for event', [
//            'event_id' => $event->id,
//            'registration_id' => $registration->id,
//            'registration_number' => $registration->registration_number,
//        ]);
//    }
//
//    /**
//     * Send payment confirmation
//     */
//    public function sendPaymentConfirmation(EventRegistration $registration)
//    {
//        $user = $registration->user;
//        if (!$user) {
//            return;
//        }
//
//        Notification::create([
//            'user_id' => $user->id,
//            'type' => 'payment_confirmed',
//            'title' => 'Payment Confirmed',
//            'message' => "Your payment for '{$registration->event->title}' has been confirmed. Registration number: {$registration->registration_number}",
//            'data' => json_encode([
//                'event_id' => $registration->event_id,
//                'registration_id' => $registration->id,
//                'payment_id' => $registration->payment_id,
//            ]),
//            'read_at' => null,
//        ]);
//
//        Log::info('Payment confirmation sent', [
//            'registration_id' => $registration->id,
//            'payment_id' => $registration->payment_id,
//        ]);
//    }
//
//    /**
//     * Send cancellation confirmation
//     */
//    public function sendCancellationConfirmation(EventRegistration $registration)
//    {
//        $user = $registration->user;
//        if (!$user) {
//            return;
//        }
//
//        $message = "Your registration for '{$registration->event->title}' has been cancelled.";
//        if ($registration->is_refund_eligible) {
//            $message .= " Your refund will be processed within 5-7 business days.";
//        }
//
//        Notification::create([
//            'user_id' => $user->id,
//            'type' => 'registration_cancelled',
//            'title' => 'Registration Cancelled',
//            'message' => $message,
//            'data' => json_encode([
//                'event_id' => $registration->event_id,
//                'registration_id' => $registration->id,
//            ]),
//            'read_at' => null,
//        ]);
//
//        Log::info('Cancellation confirmation sent', [
//            'registration_id' => $registration->id,
//        ]);
//    }
//
//    /**
//     * Send refund confirmation
//     */
//    public function sendRefundConfirmation(EventRegistration $registration)
//    {
//        $user = $registration->user;
//        if (!$user) {
//            return;
//        }
//
//        Notification::create([
//            'user_id' => $user->id,
//            'type' => 'refund_processed',
//            'title' => 'Refund Processed',
//            'message' => "Your refund for '{$registration->event->title}' has been processed. Please allow 5-7 business days for the amount to appear in your account.",
//            'data' => json_encode([
//                'event_id' => $registration->event_id,
//                'registration_id' => $registration->id,
//                'payment_id' => $registration->payment_id,
//            ]),
//            'read_at' => null,
//        ]);
//
//        Log::info('Refund confirmation sent', [
//            'registration_id' => $registration->id,
//        ]);
//    }
//
//    /**
//     * Notify when event is full
//     */
//    public function notifyEventIsFull(Event $event)
//    {
//        Log::info('Event is now full', [
//            'event_id' => $event->id,
//            'max_participants' => $event->max_participants,
//        ]);
//        
//        // TODO: Notify admins/organizers that event reached capacity
//    }
//
//    /**
//     * Notify waitlisted users when spot becomes available
//     */
//    public function notifyWaitlistedUsers(Event $event)
//    {
//        if ($event->is_full) {
//            return;
//        }
//
//        // Get next waitlisted registrations
//        $waitlisted = EventRegistration::where('event_id', $event->id)
//            ->where('status', 'waitlisted')
//            ->orderBy('created_at', 'asc')
//            ->limit($event->remaining_seats)
//            ->get();
//
//        foreach ($waitlisted as $registration) {
//            $user = $registration->user;
//            if (!$user) {
//                continue;
//            }
//
//            Notification::create([
//                'user_id' => $user->id,
//                'type' => 'spot_available',
//                'title' => 'Event Spot Available',
//                'message' => "A spot is now available for '{$event->title}'! You have 24 hours to confirm your registration.",
//                'data' => json_encode([
//                    'event_id' => $event->id,
//                    'registration_id' => $registration->id,
//                ]),
//                'read_at' => null,
//            ]);
//        }
//
//        Log::info('Waitlisted users notified', [
//            'event_id' => $event->id,
//            'count' => $waitlisted->count(),
//        ]);
//    }
//
//    /**
//     * Notify registered users about event cancellation
//     */
//    public function notifyRegisteredUsersAboutCancellation(Event $event)
//    {
//        $registrations = $event->registrations()
//                              ->where('status', 'confirmed')
//                              ->with('user')
//                              ->get();
//
//        foreach ($registrations as $registration) {
//            if (!$registration->user) {
//                continue;
//            }
//
//            Notification::create([
//                'user_id' => $registration->user_id,
//                'type' => 'event_cancelled',
//                'title' => 'Event Cancelled',
//                'message' => "The event '{$event->title}' has been cancelled. Reason: {$event->cancelled_reason}",
//                'data' => json_encode(['event_id' => $event->id]),
//                'read_at' => null,
//            ]);
//
//            // Send email notification
//            try {
//                // Mail::to($registration->user->email)->send(
//                //     new \App\Mail\EventCancelledMail($event, $registration->user)
//                // );
//            } catch (\Exception $e) {
//                Log::error('Failed to send cancellation email', [
//                    'user_id' => $registration->user_id,
//                    'error' => $e->getMessage(),
//                ]);
//            }
//        }
//
//        Log::info('Registered users notified about cancellation', [
//            'event_id' => $event->id,
//            'users_count' => $registrations->count(),
//        ]);
//    }
//
//    /**
//     * Notify registered users about event changes
//     */
//    public function notifyRegisteredUsersAboutChanges(Event $event)
//    {
//        $registrations = $event->registrations()
//                              ->where('status', 'confirmed')
//                              ->with('user')
//                              ->get();
//
//        foreach ($registrations as $registration) {
//            if (!$registration->user) {
//                continue;
//            }
//
//            Notification::create([
//                'user_id' => $registration->user_id,
//                'type' => 'event_updated',
//                'title' => 'Event Details Updated',
//                'message' => "The event '{$event->title}' details have been updated. Please check for changes.",
//                'data' => json_encode(['event_id' => $event->id]),
//                'read_at' => null,
//            ]);
//        }
//
//        Log::info('Registered users notified about changes', [
//            'event_id' => $event->id,
//            'users_count' => $registrations->count(),
//        ]);
//    }
//
//    /**
//     * Notify registered users about event deletion
//     */
//    public function notifyRegisteredUsersAboutDeletion(Event $event)
//    {
//        $registrations = $event->registrations()
//                              ->where('status', 'confirmed')
//                              ->with('user')
//                              ->get();
//
//        foreach ($registrations as $registration) {
//            if (!$registration->user) {
//                continue;
//            }
//
//            Notification::create([
//                'user_id' => $registration->user_id,
//                'type' => 'event_deleted',
//                'title' => 'Event Removed',
//                'message' => "The event '{$event->title}' has been removed from the system.",
//                'data' => json_encode(['event_id' => $event->id]),
//                'read_at' => null,
//            ]);
//        }
//
//        Log::info('Registered users notified about deletion', [
//            'event_id' => $event->id,
//            'users_count' => $registrations->count(),
//        ]);
//    }
//
//    /**
//     * Send event reminder (to be called by scheduled task)
//     */
//    public function sendEventReminders()
//    {
//        $upcomingEvents = Event::where('status', 'published')
//                               ->whereBetween('start_time', [now(), now()->addDay()])
//                               ->get();
//
//        foreach ($upcomingEvents as $event) {
//            $registrations = $event->registrations()
//                                  ->where('status', 'confirmed')
//                                  ->with('user')
//                                  ->get();
//
//            foreach ($registrations as $registration) {
//                if (!$registration->user) {
//                    continue;
//                }
//
//                Notification::create([
//                    'user_id' => $registration->user_id,
//                    'type' => 'event_reminder',
//                    'title' => 'Event Reminder',
//                    'message' => "Reminder: '{$event->title}' starts tomorrow at {$event->start_time->format('h:i A')}",
//                    'data' => json_encode(['event_id' => $event->id]),
//                    'read_at' => null,
//                ]);
//            }
//        }
//    }
//}