<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EventObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Event "creating" event.
     */
    public function creating(Event $event)
    {
        // Set created_by if authenticated
        if (auth()->check()) {
            $event->created_by = auth()->id();
            $event->updated_by = auth()->id();
        }

        // Auto-generate slug from title if needed
        if (empty($event->slug)) {
            $event->slug = \Str::slug($event->title);
        }

        Log::info('Event creating', ['title' => $event->title]);
    }

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event)
    {
        Log::info('Event created successfully', [
            'id' => $event->id,
            'title' => $event->title,
            'organizer_id' => $event->organizer_id,
        ]);

        // Clear events cache
        Cache::forget('events_upcoming');
        Cache::forget('events_public');

        // Send notification to admin about new event
        $this->notificationService->notifyAdminsAboutNewEvent($event);
    }

    /**
     * Handle the Event "updating" event.
     */
    public function updating(Event $event)
    {
        // Set updated_by
        if (auth()->check()) {
            $event->updated_by = auth()->id();
        }

        // Check if status changed to published
        if ($event->isDirty('status') && $event->status === 'published') {
            Log::info('Event being published', ['id' => $event->id]);
        }

        // Check if status changed to cancelled
        if ($event->isDirty('status') && $event->status === 'cancelled') {
            Log::warning('Event being cancelled', [
                'id' => $event->id,
                'reason' => $event->cancelled_reason,
            ]);
        }
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event)
    {
        Log::info('Event updated', ['id' => $event->id]);

        // Clear cache
        Cache::forget('events_upcoming');
        Cache::forget('events_public');
        Cache::forget('event_' . $event->id);

        // If event was published, notify all potential attendees
        if ($event->wasChanged('status') && $event->status === 'published') {
            $this->notificationService->notifyUsersAboutNewEvent($event);
        }

        // If event was cancelled, notify all registered users
        if ($event->wasChanged('status') && $event->status === 'cancelled') {
            $this->notificationService->notifyRegisteredUsersAboutCancellation($event);
        }

        // If event details changed significantly, notify registered users
        if ($event->wasChanged(['start_time', 'end_time', 'venue']) && $event->status === 'published') {
            $this->notificationService->notifyRegisteredUsersAboutChanges($event);
        }
    }

    /**
     * Handle the Event "deleting" event.
     */
    public function deleting(Event $event)
    {
        Log::warning('Event being deleted', [
            'id' => $event->id,
            'title' => $event->title,
        ]);

        // Notify registered users if event has registrations
        if ($event->registrations()->count() > 0) {
            $this->notificationService->notifyRegisteredUsersAboutDeletion($event);
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event)
    {
        Log::info('Event deleted', ['id' => $event->id]);

        // Clear cache
        Cache::forget('events_upcoming');
        Cache::forget('events_public');
        Cache::forget('event_' . $event->id);
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event)
    {
        Log::info('Event restored', ['id' => $event->id]);

        // Clear cache
        Cache::forget('events_upcoming');
        Cache::forget('events_public');
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event)
    {
        Log::warning('Event force deleted', ['id' => $event->id]);

        // Clear cache
        Cache::forget('events_upcoming');
        Cache::forget('events_public');
        Cache::forget('event_' . $event->id);
    }
}