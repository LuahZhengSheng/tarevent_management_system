<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {

    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'channel',
        'priority',
        'read_at',
        'sent_at',
    ];
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query) {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query) {
        return $query->whereNotNull('read_at');
    }

    public function scopeRecent($query) {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeForUser($query, $userId) {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type) {
        return $query->where('type', $type);
    }

    public function scopePriority($query, $priority) {
        return $query->where('priority', $priority);
    }

    // Accessors
    public function getIsReadAttribute() {
        return $this->read_at !== null;
    }

    public function getIsUnreadAttribute() {
        return $this->read_at === null;
    }

    public function getTimeAgoAttribute() {
        return $this->created_at->diffForHumans();
    }

    public function getIconAttribute() {
        return match ($this->type) {
            'event_created' => 'bi-calendar-plus',
            'event_updated' => 'bi-pencil-square',
            'event_cancelled' => 'bi-x-circle',
            'event_deleted' => 'bi-trash',
            'registration_confirmed' => 'bi-check-circle',
            'payment_confirmed' => 'bi-credit-card',
            'event_reminder' => 'bi-bell',
            'event_time_changed' => 'bi-clock',
            'event_venue_changed' => 'bi-geo-alt',
            'registration_cancelled' => 'bi-x-octagon',
            'spot_available' => 'bi-star',
            default => 'bi-info-circle',
        };
    }

    public function getColorClassAttribute() {
        return match ($this->priority) {
            'urgent' => 'text-danger',
            'high' => 'text-warning',
            'normal' => 'text-info',
            'low' => 'text-secondary',
            default => 'text-info',
        };
    }

    // Methods
    public function markAsRead() {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread() {
        $this->update(['read_at' => null]);
    }

    // Static methods for batch operations
    public static function markAllAsRead($userId) {
        return self::where('user_id', $userId)
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);
    }

    public static function deleteAllRead($userId) {
        return self::where('user_id', $userId)
                        ->whereNotNull('read_at')
                        ->delete();
    }

    public static function deleteOlderThan($userId, $days = 30) {
        return self::where('user_id', $userId)
                        ->where('created_at', '<', now()->subDays($days))
                        ->delete();
    }

    public static function sendToUser(
            int $userId,
            string $type,
            string $title,
            string $message,
            array $data = [],
            string $channel = 'database', // database / mail / both
            string $priority = 'normal'
    ) {
        return self::create([
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data, // 会被 casts 自动转成 json
                    'channel' => $channel,
                    'priority' => $priority,
                    'sent_at' => now(),
        ]);
    }
}
