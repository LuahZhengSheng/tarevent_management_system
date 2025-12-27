<?php

namespace App\Services\Club;

use App\Models\Club;
use App\Models\ClubMemberRole;
use App\Models\User;

/**
 * ClubAuthorizationService - Handles authorization checks for club actions
 *
 * This service is responsible for:
 * - Role-based access control
 * - Permission validation
 */
class ClubAuthorizationService
{
    /**
     * Ensure user can create a club.
     *
     * @param User $user The user to check
     * @throws \Exception If user cannot create clubs
     */
    public function ensureCanCreateClub(User $user): void
    {
        if (!$user->isAdministrator()) {
            throw new \Exception("Only administrators are allowed to create clubs.");
        }
    }

    /**
     * Ensure user can request to join a club.
     *
     * @param User $user The user to check
     * @throws \Exception If user cannot join clubs
     */
    public function ensureCanJoinClub(User $user): void
    {
        if ($user->role !== 'student') {
            throw new \Exception("Only students are allowed to request to join clubs.");
        }
    }

    /**
     * Ensure user can approve join requests.
     *
     * @param User $user The user to check
     * @throws \Exception If user cannot approve join requests
     */
    public function ensureCanApproveJoin(User $user): void
    {
        if ($user->role !== 'club') {
            throw new \Exception("Only club accounts are allowed to approve join requests.");
        }
    }

    /**
     * Ensure user can reject join requests.
     *
     * @param User $user The user to check
     * @throws \Exception If user cannot reject join requests
     */
    public function ensureCanRejectJoin(User $user): void
    {
        if ($user->role !== 'club') {
            throw new \Exception("Only club accounts are allowed to reject join requests.");
        }
    }

    /**
     * Ensure approver is provided.
     *
     * @param User|null $approver The approver to check
     * @throws \Exception If approver is not provided
     */
    public function ensureApproverProvided(?User $approver): void
    {
        if (!$approver) {
            throw new \Exception("Approver must be provided.");
        }
    }

    /**
     * Ensure user can manage announcements for a club.
     *
     * @param User $user The user to check
     * @param Club $club The club
     * @throws \Exception If user cannot manage announcements
     */
    public function ensureCanManageAnnouncements(User $user, Club $club): void
    {
        // Admin can manage all announcements
        if ($user->role === 'admin') {
            return;
        }

        // Club user can manage their own club's announcements
        if ($user->role === 'club' && $user->id === $club->club_user_id) {
            return;
        }

        // Club members with appropriate role can manage announcements
        if ($user->role === 'student') {
            $membership = $club->members()->where('user_id', $user->id)->first();
            if ($membership && ClubMemberRole::canManage($membership->pivot->role)) {
                return;
            }
        }

        throw new \Exception("You do not have permission to manage announcements for this club.");
    }
}


