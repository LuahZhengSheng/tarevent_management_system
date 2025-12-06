<?php

namespace App\Services;

use App\Models\Event;
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
     * Notify registered users about event cancellation
     */
    public function notifyRegisteredUsersAboutCancellation(Event $event)
    {
        $registrations = $event->registrations()
                              ->where('status', 'confirmed')
                              ->with('user')
                              ->get();

        foreach ($registrations as $registration) {
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
                Mail::to($registration->user->email)->send(
                    new \App\Mail\EventCancelledMail($event, $registration->user)
                );
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