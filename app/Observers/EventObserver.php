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
//        $this->notificationService->notifyAdminsAboutNewEvent($event);
    }

    /**
     * Handle the Event "updating" event.
     */
    public function updating(Event $event)
    {
        // Track what fields are being changed
        $dirtyFields = $event->getDirty();
        $changes = [];

        foreach ($dirtyFields as $field => $newValue) {
            $oldValue = $event->getOriginal($field);
            
            // Skip if values are the same
            if ($oldValue == $newValue) {
                continue;
            }

            $changes[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        // Store changes in a temporary cache for the updated() event
        if (!empty($changes)) {
            Cache::put("event_changes_{$event->id}", $changes, now()->addMinutes(5));
            
            Log::info('Event being updated', [
                'event_id' => $event->id,
                'changes' => array_keys($changes),
            ]);
        }
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event)
    {
        // Retrieve tracked changes
        $changes = Cache::pull("event_changes_{$event->id}");

        if (empty($changes)) {
            return;
        }

        // Clear cache
        Cache::forget('events_upcoming');
        Cache::forget('events_public');
        Cache::forget('event_' . $event->id);

        // Handle cancellation separately
        if (isset($changes['status']) && $changes['status']['new'] === 'cancelled') {
            $this->notificationService->notifyEventCancellation($event);
            return;
        }

        // Don't notify if event is past or cancelled
        if ($event->end_time < now() || $event->status === 'cancelled') {
            Log::info('Skipping notifications for past/cancelled event', [
                'event_id' => $event->id,
                'status' => $event->status,
            ]);
            return;
        }

        // Handle other updates
        $this->notificationService->handleEventUpdate($event, $changes);

        Log::info('Event updated and notifications sent', [
            'event_id' => $event->id,
            'changes' => array_keys($changes),
        ]);
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event)
    {
        Log::info('Event deleted', ['id' => $event->id]);

        // Unsubscribe all users
        $this->notificationService->unsubscribeFromEvent(null, $event->id, 'event_deleted');

        // Clear cache
        Cache::forget('events_upcoming');
        Cache::forget('events_public');
        Cache::forget('event_' . $event->id);
    }
}