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
}

