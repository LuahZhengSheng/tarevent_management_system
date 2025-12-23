<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSubscription extends Model {

    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
        'reason',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];
    protected $attributes = [
        'is_active' => true,
    ];

    // Relationships
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function event() {
        return $this->belongsTo(Event::class);
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeForEvent($query, $eventId) {
        return $query->where('event_id', $eventId);
    }

    public function scopeForUser($query, $userId) {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function unsubscribe($reason = null) {
        $this->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
            'reason' => $reason,
        ]);
    }

    public function resubscribe() {
        $this->update([
            'is_active' => true,
            'unsubscribed_at' => null,
            'reason' => null,
        ]);
    }

    // Static methods
    public static function subscribe($userId, $eventId) {
        return self::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'event_id' => $eventId,
                        ],
                        [
                            'is_active' => true,
                            'subscribed_at' => now(),
                            'unsubscribed_at' => null,
                            'reason' => null,
                        ]
        );
    }

    public static function unsubscribeFromEvent($userId, $eventId, $reason = null) {
        $subscription = self::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->first();

        if ($subscription) {
            $subscription->unsubscribe($reason);
        }
    }

    public static function isSubscribed($userId, $eventId) {
        return self::where('user_id', $userId)
                        ->where('event_id', $eventId)
                        ->where('is_active', true)
                        ->exists();
    }

    // Boot method
    protected static function boot() {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->subscribed_at)) {
                $subscription->subscribed_at = now();
            }
        });
    }
}
