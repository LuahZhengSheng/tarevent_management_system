<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Models\Traits;

trait HasNotifications
{
    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get recent unread notifications
     */
    public function getRecentUnreadNotificationsAttribute()
    {
        return $this->notifications()
            ->unread()
            ->recent()
            ->limit(5)
            ->get();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        return $this->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}

