<?php

namespace App\Services\Club;

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
        if ($user->role !== 'admin') {
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
}


