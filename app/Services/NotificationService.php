<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify admins about new event creation
     */
    public function notifyAdminsAboutNewEvent(Event $event)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'event_created',
                'title' => 'New Event Created',
                'message' => "A new event '{$event->title}' has been created and requires approval.",
                'data' => json_encode(['event_id' => $event->id]),
                'read_at' => null,
            ]);
        }

        Log::info('Admins notified about new event', ['event_id' => $event->id]);
    }

    /**
     * Notify users about published event
     */
    public function notifyUsersAboutNewEvent(Event $event)
    {
        // Only notify for public events
        if (!$event->is_public) {
            return;
        }

        // Get users interested in this category (implement as needed)
        $users = User::where('role', 'student')
                     ->whereJsonContains('interested_categories', $event->category)
                     ->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'new_event',
                'title' => 'New Event Available',
                'message' => "Check out the new event: {$event->title}",
                'data' => json_encode(['event_id' => $event->id]),
                'read_at' => null,
            ]);
        }

        Log::info('Users notified about published event', ['event_id' => $event->id]);
    }

    /**
     * Send registration confirmation to user
     */
    public function sendRegistrationConfirmation(EventRegistration $registration)
    {
        $event = $registration->event;
        $user = $registration->user;

        if (!$user) {
            return;
        }

        $message = $registration->status === 'pending_payment'
            ? "You have successfully registered for '{$event->title}'. Please complete payment to confirm your spot."
            : "Your registration for '{$event->title}' has been confirmed! Registration number: {$registration->registration_number}";

        Notification::create([
            'user_id' => $user->id,
            'type' => 'registration_confirmed',
            'title' => 'Registration Received',
            'message' => $message,
            'data' => json_encode([
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'registration_number' => $registration->registration_number,
            ]),
            'read_at' => null,
        ]);

        // TODO: Send email confirmation
        // Mail::to($user->email)->send(new RegistrationConfirmationMail($registration));

        Log::info('Registration confirmation sent', [
            'registration_id' => $registration->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Notify organizer about new registration
     */
    public function notifyOrganizerAboutNewRegistration(EventRegistration $registration)
    {
        $event = $registration->event;
        
        // TODO: Get club admins/organizers
        // For now, just log it
        Log::info('New registration for event', [
            'event_id' => $event->id,
            'registration_id' => $registration->id,
            'registration_number' => $registration->registration_number,
        ]);
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(EventRegistration $registration)
    {
        $user = $registration->user;
        if (!$user) {
            return;
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => 'payment_confirmed',
            'title' => 'Payment Confirmed',
            'message' => "Your payment for '{$registration->event->title}' has been confirmed. Registration number: {$registration->registration_number}",
            'data' => json_encode([
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
                'payment_id' => $registration->payment_id,
            ]),
            'read_at' => null,
        ]);

        Log::info('Payment confirmation sent', [
            'registration_id' => $registration->id,
            'payment_id' => $registration->payment_id,
        ]);
    }

    /**
     * Send cancellation confirmation
     */
    public function sendCancellationConfirmation(EventRegistration $registration)
    {
        $user = $registration->user;
        if (!$user) {
            return;
        }

        $message = "Your registration for '{$registration->event->title}' has been cancelled.";
        if ($registration->is_refund_eligible) {
            $message .= " Your refund will be processed within 5-7 business days.";
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => 'registration_cancelled',
            'title' => 'Registration Cancelled',
            'message' => $message,
            'data' => json_encode([
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
            ]),
            'read_at' => null,
        ]);

        Log::info('Cancellation confirmation sent', [
            'registration_id' => $registration->id,
        ]);
    }

    /**
     * Send refund confirmation
     */
    public function sendRefundConfirmation(EventRegistration $registration)
    {
        $user = $registration->user;
        if (!$user) {
            return;
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => 'refund_processed',
            'title' => 'Refund Processed',
            'message' => "Your refund for '{$registration->event->title}' has been processed. Please allow 5-7 business days for the amount to appear in your account.",
            'data' => json_encode([
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
                'payment_id' => $registration->payment_id,
            ]),
            'read_at' => null,
        ]);

        Log::info('Refund confirmation sent', [
            'registration_id' => $registration->id,
        ]);
    }

    /**
     * Notify when event is full
     */
    public function notifyEventIsFull(Event $event)
    {
        Log::info('Event is now full', [
            'event_id' => $event->id,
            'max_participants' => $event->max_participants,
        ]);
        
        // TODO: Notify admins/organizers that event reached capacity
    }

    /**
     * Notify waitlisted users when spot becomes available
     */
    public function notifyWaitlistedUsers(Event $event)
    {
        if ($event->is_full) {
            return;
        }

        // Get next waitlisted registrations
        $waitlisted = EventRegistration::where('event_id', $event->id)
            ->where('status', 'waitlisted')
            ->orderBy('created_at', 'asc')
            ->limit($event->remaining_seats)
            ->get();

        foreach ($waitlisted as $registration) {
            $user = $registration->user;
            if (!$user) {
                continue;
            }

            Notification::create([
                'user_id' => $user->id,
                'type' => 'spot_available',
                'title' => 'Event Spot Available',
                'message' => "A spot is now available for '{$event->title}'! You have 24 hours to confirm your registration.",
                'data' => json_encode([
                    'event_id' => $event->id,
                    'registration_id' => $registration->id,
                ]),
                'read_at' => null,
            ]);
        }

        Log::info('Waitlisted users notified', [
            'event_id' => $event->id,
            'count' => $waitlisted->count(),
        ]);
    }

    /**
     * Notify registered users about event cancellation
     */
    public function notifyRegisteredUsersAboutCancellation(Event $event)
    {
        $registrations = $event->registrations()
                              ->where('status', 'confirmed')
                              ->with('user')
                              ->get();

        foreach ($registrations as $registration) {
            if (!$registration->user) {
                continue;
            }

            Notification::create([
                'user_id' => $registration->user_id,
                'type' => 'event_cancelled',
                'title' => 'Event Cancelled',
                'message' => "The event '{$event->title}' has been cancelled. Reason: {$event->cancelled_reason}",
                'data' => json_encode(['event_id' => $event->id]),
                'read_at' => null,
            ]);

            // Send email notification
            try {
                // Mail::to($registration->user->email)->send(
                //     new \App\Mail\EventCancelledMail($event, $registration->user)
                // );
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation email', [
                    'user_id' => $registration->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Registered users notified about cancellation', [
            'event_id' => $event->id,
            'users_count' => $registrations->count(),
        ]);
    }

    /**
     * Notify registered users about event changes
     */
    public function notifyRegisteredUsersAboutChanges(Event $event)
    {
        $registrations = $event->registrations()
                              ->where('status', 'confirmed')
                              ->with('user')
                              ->get();

        foreach ($registrations as $registration) {
            if (!$registration->user) {
                continue;
            }

            Notification::create([
                'user_id' => $registration->user_id,
                'type' => 'event_updated',
                'title' => 'Event Details Updated',
                'message' => "The event '{$event->title}' details have been updated. Please check for changes.",
                'data' => json_encode(['event_id' => $event->id]),
                'read_at' => null,
            ]);
        }

        Log::info('Registered users notified about changes', [
            'event_id' => $event->id,
            'users_count' => $registrations->count(),
        ]);
    }

    /**
     * Notify registered users about event deletion
     */
    public function notifyRegisteredUsersAboutDeletion(Event $event)
    {
        $registrations = $event->registrations()
                              ->where('status', 'confirmed')
                              ->with('user')
                              ->get();

        foreach ($registrations as $registration) {
            if (!$registration->user) {
                continue;
            }

            Notification::create([
                'user_id' => $registration->user_id,
                'type' => 'event_deleted',
                'title' => 'Event Removed',
                'message' => "The event '{$event->title}' has been removed from the system.",
                'data' => json_encode(['event_id' => $event->id]),
                'read_at' => null,
            ]);
        }

        Log::info('Registered users notified about deletion', [
            'event_id' => $event->id,
            'users_count' => $registrations->count(),
        ]);
    }

    /**
     * Send event reminder (to be called by scheduled task)
     */
    public function sendEventReminders()
    {
        $upcomingEvents = Event::where('status', 'published')
                               ->whereBetween('start_time', [now(), now()->addDay()])
                               ->get();

        foreach ($upcomingEvents as $event) {
            $registrations = $event->registrations()
                                  ->where('status', 'confirmed')
                                  ->with('user')
                                  ->get();

            foreach ($registrations as $registration) {
                if (!$registration->user) {
                    continue;
                }

                Notification::create([
                    'user_id' => $registration->user_id,
                    'type' => 'event_reminder',
                    'title' => 'Event Reminder',
                    'message' => "Reminder: '{$event->title}' starts tomorrow at {$event->start_time->format('h:i A')}",
                    'data' => json_encode(['event_id' => $event->id]),
                    'read_at' => null,
                ]);
            }
        }
    }
}