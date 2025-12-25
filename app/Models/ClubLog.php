<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'club_id',
        'action',
        'actor_id',
        'target_user_id',
        'metadata',
        'request_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the actor (user who performed the action).
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the target user (if applicable).
     */
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Get the club.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}

