<?php

namespace App\Services\Club;

use App\Models\Club;
use App\Models\ClubLog;
use App\Models\User;

/**
 * ClubAuditService - Handles audit logging for club actions
 * 
 * This service is responsible for:
 * - Logging all club-related actions
 * - Maintaining audit trail
 */
class ClubAuditService
{
    /**
     * Log an action for audit purposes.
     * 
     * @param Club $club The club
     * @param string $action The action performed
     * @param User $actor The user who performed the action
     * @param User|null $target Optional target user
     * @param array $meta Optional metadata
     * @return void
     */
    public function log(Club $club, string $action, User $actor, ?User $target = null, array $meta = []): void
    {
        ClubLog::create([
            'club_id' => $club->id,
            'action' => $action,
            'actor_id' => $actor->id,
            'target_user_id' => $target?->id,
            'metadata' => $meta,
        ]);
    }
}


