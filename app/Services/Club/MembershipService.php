<?php

namespace App\Services\Club;

use App\Models\Club;
use App\Models\ClubBlacklist;
use App\Models\ClubJoinRequest;
use App\Models\ClubMemberRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MembershipService - Handles club membership and join request business logic
 * 
 * This service is responsible for:
 * - Join request management (create, approve, reject)
 * - Adding and removing members
 * - Managing member roles
 * - Membership queries
 */
class MembershipService
{
    /**
     * Add a member to a club with specified role.
     * 
     * @param Club $club The club to add member to
     * @param User $user The user to add as member
     * @param string|null $role Optional role (defaults to 'member')
     * @return bool True if member was added, false if already a member
     * @throws \Exception If user cannot be added or role is invalid
     */
    public function addMember(Club $club, User $user, ?string $role = null): bool
    {
        // Validate that user is a student (not admin or club account)
        if (!in_array($user->role, ['user', 'student'])) {
            throw new \Exception("Only students can be added as club members.");
        }

        // Validate role if provided
        $finalRole = $role ?? ClubMemberRole::MEMBER;
        if (!ClubMemberRole::isValid($finalRole)) {
            throw new \Exception("Invalid member role: {$finalRole}. Valid roles are: " . implode(', ', ClubMemberRole::all()));
        }

        // Check if user is already a member
        if ($this->hasMember($club, $user)) {
            return false;
        }

        // Add user with specified role or default to 'member'
        // If user was previously removed, update status to 'active' instead of creating duplicate
        $existingPivot = $club->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'removed')
            ->first();

        if ($existingPivot) {
            // Update existing record to active
            $club->members()->updateExistingPivot($user->id, [
                'role' => $finalRole,
                'status' => 'active',
            ]);
        } else {
            // Create new record
            $club->members()->attach($user->id, [
                'role' => $finalRole,
                'status' => 'active',
            ]);
        }

        return true;
    }

    /**
     * Remove a member from a club.
     * Sets status to 'removed' instead of deleting the record.
     * 
     * @param Club $club The club to remove member from
     * @param User $user The user to remove
     * @return bool True if member was removed
     * @throws \Exception If removal is not allowed
     */
    public function removeMember(Club $club, User $user): bool
    {
        // Check if user is a member of the club
        if (!$this->hasMember($club, $user)) {
            throw new \Exception("User is not a member of this club.");
        }

        // Validate that user is a student (not admin or club account)
        if (!in_array($user->role, ['user', 'student'])) {
            throw new \Exception("Only student members can be removed from clubs.");
        }

        // Update status to 'removed' instead of detaching
        $club->members()->updateExistingPivot($user->id, [
            'status' => 'removed',
        ]);

        return true;
    }

    /**
     * Update a member's role in a club.
     * 
     * @param Club $club The club
     * @param User $user The member whose role to update
     * @param string $newRole The new role to assign
     * @return bool True if role was updated
     * @throws \Exception If role update is not allowed or role is invalid
     */
    public function updateMemberRole(Club $club, User $user, string $newRole): bool
    {
        // Check if user is a member of the club
        if (!$this->hasMember($club, $user)) {
            throw new \Exception("User is not a member of this club.");
        }

        // Validate role
        if (!ClubMemberRole::isValid($newRole)) {
            throw new \Exception("Invalid member role: {$newRole}. Valid roles are: " . implode(', ', ClubMemberRole::all()));
        }

        // Update the role in the pivot table
        $club->members()->updateExistingPivot($user->id, [
            'role' => $newRole,
        ]);

        return true;
    }

    /**
     * List all members of a club.
     * 
     * @param Club $club The club
     * @return Collection|User[] Collection of member users
     */
    public function listMembers(Club $club): Collection
    {
        return $club->members()->get();
    }

    /**
     * Check if a user is an active member of a club.
     * Only checks for members with status = 'active'.
     * 
     * @param Club $club The club
     * @param User $user The user to check
     * @return bool True if user is an active member
     */
    public function hasMember(Club $club, User $user): bool
    {
        return $club->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Get the role of a member in a club.
     * 
     * @param Club $club The club
     * @param User $user The member user
     * @return string|null The member's role, or null if not a member
     */
    public function getMemberRole(Club $club, User $user): ?string
    {
        $member = $club->members()->where('users.id', $user->id)->first();
        
        return $member?->pivot->role ?? null;
    }

    /**
     * Count the number of members in a club.
     * 
     * @param Club $club The club
     * @return int Number of members
     */
    public function countMembers(Club $club): int
    {
        return $club->members()->count();
    }

    /**
     * Ensure user is not already a member.
     * 
     * @param Club $club The club
     * @param User $user The user to check
     * @throws \Exception If user is already a member
     */
    public function ensureNotAlreadyMember(Club $club, User $user): void
    {
        if ($this->hasMember($club, $user)) {
            throw new \Exception("User is already a member of this club.");
        }
    }

    // ============================================
    // Join Request Management Methods
    // ============================================

    /**
     * Create a join request.
     * 
     * @param Club $club The club to join
     * @param User $user The user requesting to join
     * @param string|null $description Optional description/reason for joining
     * @return ClubJoinRequest The created join request
     * @throws \Exception If request cannot be created
     */
    public function createJoinRequest(Club $club, User $user, ?string $description = null): ClubJoinRequest
    {
        // Check for existing request (any status) to avoid unique constraint violation
        $existingRequest = ClubJoinRequest::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->first();

        return DB::transaction(function () use ($club, $user, $description, $existingRequest) {
            if ($existingRequest) {
                // If request exists, update it to pending status with new description
                $existingRequest->status = 'pending';
                $existingRequest->description = $description;
                $existingRequest->save();
                return $existingRequest;
            } else {
                // Create new request if none exists
                return ClubJoinRequest::create([
                    'club_id' => $club->id,
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'description' => $description,
                ]);
            }
        });
    }

    /**
     * Approve a join request.
     * 
     * @param Club $club The club
     * @param User $user The user whose request to approve
     * @return ClubJoinRequest The approved request
     * @throws \Exception If request cannot be approved
     */
    public function approveJoinRequest(Club $club, User $user): ClubJoinRequest
    {
        $request = $this->findPendingJoinRequest($club, $user);

        if (!$request) {
            throw new \Exception("No pending join request found for this user.");
        }

        return DB::transaction(function () use ($request) {
            $request->status = 'approved';
            $request->save();

            return $request;
        });
    }

    /**
     * Reject a join request.
     * 
     * @param Club $club The club
     * @param User $user The user whose request to reject
     * @return ClubJoinRequest The rejected request
     * @throws \Exception If request cannot be rejected
     */
    public function rejectJoinRequest(Club $club, User $user): ClubJoinRequest
    {
        $request = $this->findPendingJoinRequest($club, $user);

        if (!$request) {
            throw new \Exception("No pending join request found for this user.");
        }

        return DB::transaction(function () use ($request) {
            $request->status = 'rejected';
            $request->save();

            return $request;
        });
    }

    /**
     * Find a pending join request.
     * 
     * @param Club $club The club
     * @param User $user The user
     * @return ClubJoinRequest|null
     */
    public function findPendingJoinRequest(Club $club, User $user): ?ClubJoinRequest
    {
        return ClubJoinRequest::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
    }

    /**
     * Ensure no pending request exists.
     * 
     * @param Club $club The club
     * @param User $user The user
     * @throws \Exception If pending request exists
     */
    public function ensureNoPendingJoinRequest(Club $club, User $user): void
    {
        if ($this->findPendingJoinRequest($club, $user)) {
            throw new \Exception("A pending join request already exists for this user.");
        }
    }

    /**
     * Get the join status of a user for a club.
     * 
     * @param Club $club The club
     * @param User $user The user
     * @return array Status information with keys:
     *   - status: 'available' | 'member' | 'pending' | 'rejected' | 'rejected_cooldown' | 'removed' | 'removed_cooldown' | 'blacklisted'
     *   - rejected_at: Carbon|null
     *   - removed_at: Carbon|null
     *   - cooldown_remaining_days: int|null
     *   - pending_request_id: int|null
     *   - blacklist_reason: string|null
     */
    public function getClubJoinStatus(Club $club, User $user): array
    {
        // First check if user is blacklisted
        $blacklist = ClubBlacklist::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->first();

        if ($blacklist) {
            return [
                'status' => 'blacklisted',
                'rejected_at' => null,
                'removed_at' => null,
                'cooldown_remaining_days' => null,
                'pending_request_id' => null,
                'blacklist_reason' => $blacklist->reason,
            ];
        }

        // Check if user is already an active member
        if ($this->hasMember($club, $user)) {
            return [
                'status' => 'member',
                'rejected_at' => null,
                'removed_at' => null,
                'cooldown_remaining_days' => null,
                'pending_request_id' => null,
                'blacklist_reason' => null,
            ];
        }

        // Check if user was removed (status = 'removed' in club_user table)
        $removedMember = $club->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'removed')
            ->first();

        if ($removedMember) {
            $removedAt = $removedMember->pivot->updated_at;
            // Use absolute value to handle both past and future dates correctly
            $daysSinceRemoval = abs(Carbon::now()->diffInDays($removedAt, false));

            if ($daysSinceRemoval < 3) {
                // Still in cooldown - use ceil to round up to nearest integer
                $cooldownDays = (int) ceil(3 - $daysSinceRemoval);
                return [
                    'status' => 'removed',
                    'rejected_at' => null,
                    'removed_at' => $removedAt,
                    'cooldown_remaining_days' => $cooldownDays,
                    'pending_request_id' => null,
                    'blacklist_reason' => null,
                ];
            } else {
                // Cooldown expired, can retry
                return [
                    'status' => 'removed_cooldown',
                    'rejected_at' => null,
                    'removed_at' => $removedAt,
                    'cooldown_remaining_days' => null,
                    'pending_request_id' => null,
                    'blacklist_reason' => null,
                ];
            }
        }

        // Check for any join request (pending, approved, or rejected)
        $joinRequest = ClubJoinRequest::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$joinRequest) {
            return [
                'status' => 'available',
                'rejected_at' => null,
                'removed_at' => null,
                'cooldown_remaining_days' => null,
                'pending_request_id' => null,
                'blacklist_reason' => null,
            ];
        }

        // Check status
        if ($joinRequest->status === 'pending') {
            return [
                'status' => 'pending',
                'rejected_at' => null,
                'removed_at' => null,
                'cooldown_remaining_days' => null,
                'pending_request_id' => $joinRequest->id,
                'blacklist_reason' => null,
            ];
        }

        if ($joinRequest->status === 'rejected') {
            $cooldownDays = $joinRequest->getCooldownRemainingDays();
            
            if ($cooldownDays === null) {
                // Cooldown expired, can retry
                return [
                    'status' => 'rejected_cooldown',
                    'rejected_at' => $joinRequest->updated_at,
                    'removed_at' => null,
                    'cooldown_remaining_days' => null,
                    'pending_request_id' => null,
                    'blacklist_reason' => null,
                ];
            } else {
                // Still in cooldown
                return [
                    'status' => 'rejected',
                    'rejected_at' => $joinRequest->updated_at,
                    'removed_at' => null,
                    'cooldown_remaining_days' => $cooldownDays,
                    'pending_request_id' => null,
                    'blacklist_reason' => null,
                ];
            }
        }

        // If approved, user should be a member (but check anyway)
        if ($joinRequest->status === 'approved') {
            // Check if user was removed after approval
            if ($removedMember) {
                $removedAt = $removedMember->pivot->updated_at;
                // Use absolute value to handle both past and future dates correctly
                $daysSinceRemoval = abs(Carbon::now()->diffInDays($removedAt, false));
                
                if ($daysSinceRemoval < 3) {
                    // Use ceil to round up to nearest integer
                    $cooldownDays = (int) ceil(3 - $daysSinceRemoval);
                    return [
                        'status' => 'removed',
                        'rejected_at' => null,
                        'removed_at' => $removedAt,
                        'cooldown_remaining_days' => $cooldownDays,
                        'pending_request_id' => null,
                        'blacklist_reason' => null,
                    ];
                } else {
                    return [
                        'status' => 'removed_cooldown',
                        'rejected_at' => null,
                        'removed_at' => $removedAt,
                        'cooldown_remaining_days' => null,
                        'pending_request_id' => null,
                        'blacklist_reason' => null,
                    ];
                }
            }
            
            return [
                'status' => 'member',
                'rejected_at' => null,
                'removed_at' => null,
                'cooldown_remaining_days' => null,
                'pending_request_id' => null,
                'blacklist_reason' => null,
            ];
        }

        // Default: available
        return [
            'status' => 'available',
            'rejected_at' => null,
            'removed_at' => null,
            'cooldown_remaining_days' => null,
            'pending_request_id' => null,
            'blacklist_reason' => null,
        ];
    }

    // ============================================
    // Blacklist Management Methods
    // ============================================

    /**
     * Add a user to club blacklist.
     * If the user is currently a member, they will be removed from the club first.
     * 
     * @param Club $club The club
     * @param User $user The user to blacklist
     * @param string|null $reason Optional reason for blacklisting
     * @param User|null $blacklistedBy The user performing the blacklist action
     * @return ClubBlacklist The created blacklist entry
     * @throws \Exception If user is already blacklisted
     */
    public function addToBlacklist(Club $club, User $user, ?string $reason = null, ?User $blacklistedBy = null): ClubBlacklist
    {
        // Check if already blacklisted
        $existing = ClubBlacklist::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            throw new \Exception("User is already blacklisted from this club.");
        }

        return DB::transaction(function () use ($club, $user, $reason, $blacklistedBy) {
            // If user is currently a member, remove them from the club first
            if ($this->hasMember($club, $user)) {
                // Update status to 'removed' instead of detaching
                $club->members()->updateExistingPivot($user->id, [
                    'status' => 'removed',
                ]);
            }

            // Create blacklist entry
            return ClubBlacklist::create([
                'club_id' => $club->id,
                'user_id' => $user->id,
                'reason' => $reason,
                'blacklisted_by' => $blacklistedBy?->id,
            ]);
        });
    }

    /**
     * Remove a user from club blacklist.
     * 
     * @param Club $club The club
     * @param User $user The user to remove from blacklist
     * @return bool True if removed successfully
     * @throws \Exception If user is not blacklisted
     */
    public function removeFromBlacklist(Club $club, User $user): bool
    {
        $blacklist = ClubBlacklist::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$blacklist) {
            throw new \Exception("User is not blacklisted from this club.");
        }

        $blacklist->delete();
        return true;
    }

    /**
     * Check if a user is blacklisted from a club.
     * 
     * @param Club $club The club
     * @param User $user The user to check
     * @return bool True if user is blacklisted
     */
    public function isBlacklisted(Club $club, User $user): bool
    {
        return ClubBlacklist::where('club_id', $club->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Clear cooldown period for a removed member.
     * This allows the user to immediately request to join again.
     * 
     * @param Club $club The club
     * @param User $user The user whose cooldown to clear
     * @return bool True if cooldown was cleared
     * @throws \Exception If user is not in cooldown period
     */
    public function clearMemberCooldown(Club $club, User $user): bool
    {
        // Check if user has a removed status
        $removedMember = $club->members()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'removed')
            ->first();

        if (!$removedMember) {
            throw new \Exception("User is not in cooldown period (not removed from this club).");
        }

        return DB::transaction(function () use ($club, $user) {
            // Delete the club_user record to completely remove the cooldown
            // This allows the user to immediately request to join again
            DB::table('club_user')
                ->where('club_id', $club->id)
                ->where('user_id', $user->id)
                ->where('status', 'removed')
                ->delete();

            return true;
        });
    }
}


