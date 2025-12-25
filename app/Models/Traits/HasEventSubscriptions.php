<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Models\Traits;

use App\Models\EventSubscription;

trait HasEventSubscriptions
{
    /**
     * Check if user is subscribed to an event
     */
    public function isSubscribedToEvent($eventId): bool
    {
        return $this->eventSubscriptions()
            ->where('event_id', $eventId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Subscribe to event
     */
    public function subscribeToEvent($eventId)
    {
        return EventSubscription::subscribe($this->id, $eventId);
    }

    /**
     * Unsubscribe from event
     */
    public function unsubscribeFromEvent($eventId, $reason = null): void
    {
        EventSubscription::unsubscribeFromEvent($this->id, $eventId, $reason);
    }
}

