<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Models\Traits;

use App\Models\Event;

trait HasEventPermissions
{
    /**
     * Check if user can create events
     */
    public function canCreateEvent(): bool
    {
        return $this->isClub() || $this->isAdmin();
    }

    /**
     * Check if user can edit a specific event
     */
    public function canEditEvent(Event $event): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isClub() && $event->organizer_type === 'club') {
            return $event->organizer_id === $this->club_id;
        }

        return false;
    }

    /**
     * Check if user can delete a specific event
     */
    public function canDeleteEvent(Event $event): bool
    {
        return $this->canEditEvent($event);
    }

    /**
     * Check if user can register for a specific event
     */
    public function canRegisterForEvent(Event $event): bool
    {
        // Check if user is active
        if (!$this->isActive()) {
            return false;
        }

        // Check if already registered
        if ($this->isRegisteredForEvent($event)) {
            return false;
        }

        // Check if event is open for registration
        return $event->is_registration_open;
    }

    /**
     * Check if user is registered for a specific event
     */
    public function isRegisteredForEvent(Event $event): bool
    {
        return $this->eventRegistrations()
            ->where('event_id', $event->id)
            ->whereIn('status', ['confirmed', 'pending_payment'])
            ->exists();
    }
}

