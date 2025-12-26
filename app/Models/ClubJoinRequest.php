<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClubJoinRequest extends Model
{
    protected $fillable = [
        'club_id',
        'user_id',
        'status',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the join request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the club for this join request.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Check if the request is rejected.
     * 
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the user can retry after rejection (3 days cooldown period).
     * 
     * @return bool
     */
    public function canRetryAfterRejection(): bool
    {
        if (!$this->isRejected()) {
            return false;
        }

        // Check if 3 days have passed since rejection (updated_at is when status changed to rejected)
        $rejectedAt = $this->updated_at;
        $threeDaysAgo = Carbon::now()->subDays(3);

        return $rejectedAt->lessThanOrEqualTo($threeDaysAgo);
    }

    /**
     * Get the remaining cooldown days after rejection.
     * 
     * @return int|null Number of days remaining, or null if not rejected or cooldown expired
     */
    public function getCooldownRemainingDays(): ?int
    {
        if (!$this->isRejected()) {
            return null;
        }

        $rejectedAt = $this->updated_at;
        $daysSinceRejection = Carbon::now()->diffInDays($rejectedAt, false);

        if ($daysSinceRejection >= 3) {
            return null; // Cooldown expired
        }

        // Use ceil to round up to nearest integer
        return (int) ceil(3 - $daysSinceRejection);
    }
}

