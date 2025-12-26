<?php

namespace App\Services;

use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\User;
use App\Services\Club\ClubService;
use App\Services\Club\MembershipService;
use App\Services\Club\ClubAuditService;
use App\Services\Club\ClubAuthorizationService;
use App\Services\Club\AnnouncementService;
use Illuminate\Database\Eloquent\Collection;

/**
 * ClubFacade - Facade Pattern Implementation
 * 
 * Provides a simplified interface to the complex Club subsystem.
 * This facade coordinates multiple services without containing business logic.
 * 
 * Responsibilities:
 * - Coordinate services (ClubService, MembershipService, JoinRequestService, etc.)
 * - Provide simplified API for controllers
 * - Hide subsystem complexity
 * 
 * This facade does NOT:
 * - Contain business logic
 * - Directly access models
 * - Perform data validation beyond delegation
 */
class ClubFacade
{
    protected ClubService $clubService;
    protected MembershipService $membershipService;
    protected ClubAuditService $auditService;
    protected ClubAuthorizationService $authorizationService;
    protected AnnouncementService $announcementService;

    public function __construct(
        ClubService $clubService,
        MembershipService $membershipService,
        ClubAuditService $auditService,
        ClubAuthorizationService $authorizationService,
        AnnouncementService $announcementService
    ) {
        $this->clubService = $clubService;
        $this->membershipService = $membershipService;
        $this->auditService = $auditService;
        $this->authorizationService = $authorizationService;
        $this->announcementService = $announcementService;
    }

    /**
     * Create a new club.
     * 
     * Facade method: Coordinates authorization, creation, and audit logging.
     * 
     * @param array $data Club data
     * @param User $creator The user creating the club
     * @return Club
     * @throws \Exception If creation fails
     */
    public function createClub(array $data, User $creator): Club
    {
        // Delegate authorization check
        $this->authorizationService->ensureCanCreateClub($creator);

        // Delegate club creation
        $club = $this->clubService->create($data, $creator);

        // Delegate audit logging
        $this->auditService->log($club, 'create_club', $creator);

        return $club;
    }

    /**
     * Approve a pending club.
     * 
     * @param Club $club The club to approve
     * @param User $approver The user approving the club
     * @param bool $activate Whether to set status to 'active' immediately
     * @return Club
     */
    public function approveClub(Club $club, User $approver, bool $activate = true): Club
    {
        // Delegate club approval
        $club = $this->clubService->approve($club, $approver, $activate);

        // Delegate audit logging
        $this->auditService->log($club, 'approve_club', $approver);

        return $club;
    }

    /**
     * Reject a pending club.
     * 
     * @param Club $club The club to reject
     * @param User $rejector The user rejecting the club
     * @param string|null $reason Optional rejection reason
     * @return Club
     */
    public function rejectClub(Club $club, User $rejector, ?string $reason = null): Club
    {
        // Delegate club rejection
        $club = $this->clubService->reject($club, $rejector, $reason);

        // Delegate audit logging
        $this->auditService->log($club, 'reject_club', $rejector);

        return $club;
    }

    /**
     * Activate a club.
     * 
     * @param Club $club The club to activate
     * @param User|null $activatedBy The user activating the club
     * @return Club
     */
    public function activateClub(Club $club, ?User $activatedBy = null): Club
    {
        // Delegate club activation
        $club = $this->clubService->activate($club);

        // Delegate audit logging if actor provided
        if ($activatedBy) {
            $this->auditService->log($club, 'activate_club', $activatedBy);
        }

        return $club;
    }

    /**
     * Deactivate a club.
     * 
     * @param Club $club The club to deactivate
     * @param User|null $deactivatedBy The user deactivating the club
     * @param string|null $reason Optional reason for deactivation
     * @return Club
     */
    public function deactivateClub(Club $club, ?User $deactivatedBy = null, ?string $reason = null): Club
    {
        // Delegate club deactivation
        $club = $this->clubService->deactivate($club, $reason);

        // Delegate audit logging if actor provided
        if ($deactivatedBy) {
            $this->auditService->log($club, 'deactivate_club', $deactivatedBy, null, ['reason' => $reason]);
        }

        return $club;
    }

    /**
     * Add a member to a club.
     * 
     * @param Club $club The club to add member to
     * @param User $user The user to add as member
     * @param string|null $role Optional role
     * @return bool True if member was added
     */
    public function addMember(Club $club, User $user, ?string $role = null): bool
    {
        // Delegate member addition
        $result = $this->membershipService->addMember($club, $user, $role);

        // Delegate audit logging if member was added
        if ($result) {
            $actor = auth()->user() ?? $club->creator;
            $this->auditService->log($club, 'add_member', $actor, $user);
        }

        return $result;
    }

    /**
     * Remove a member from a club.
     * 
     * @param Club $club The club to remove member from
     * @param User $user The user to remove
     * @param User|null $removedBy The user performing the removal
     * @return bool True if member was removed
     */
    public function removeMember(Club $club, User $user, ?User $removedBy = null): bool
    {
        // Delegate member removal
        $this->membershipService->removeMember($club, $user);

        // Delegate audit logging
        $actor = $removedBy ?? auth()->user() ?? $club->creator;
        $this->auditService->log($club, 'remove_member', $actor, $user);

        return true;
    }

    /**
     * Update a member's role in a club.
     * 
     * @param Club $club The club
     * @param User $user The member whose role to update
     * @param string $newRole The new role to assign
     * @param User|null $updatedBy The user making the change
     * @return bool True if role was updated
     */
    public function updateMemberRole(Club $club, User $user, string $newRole, ?User $updatedBy = null): bool
    {
        // Delegate role update
        $this->membershipService->updateMemberRole($club, $user, $newRole);

        // Delegate audit logging
        $actor = $updatedBy ?? auth()->user() ?? $club->creator;
        $this->auditService->log($club, 'update_member_role', $actor, $user, ['new_role' => $newRole]);

        return true;
    }

    /**
     * Request to join a club.
     * 
     * @param Club $club The club to join
     * @param User $user The user requesting to join
     * @param string|null $description Optional description/reason for joining
     * @return ClubJoinRequest The created join request
     */
    public function requestJoin(Club $club, User $user, ?string $description = null): ClubJoinRequest
    {
        // Delegate authorization check
        $this->authorizationService->ensureCanJoinClub($user);

        // Check if user is blacklisted
        if ($this->membershipService->isBlacklisted($club, $user)) {
            throw new \Exception("You are blacklisted from this club and cannot submit join requests.");
        }

        // Get join status to check for removed cooldown
        $joinStatus = $this->membershipService->getClubJoinStatus($club, $user);
        
        if ($joinStatus['status'] === 'removed') {
            $cooldownDays = (int) ceil($joinStatus['cooldown_remaining_days']);
            throw new \Exception("You were removed from this club. Please wait {$cooldownDays} more day(s) before requesting to join again.");
        }

        // Delegate membership check
        $this->membershipService->ensureNotAlreadyMember($club, $user);

        // Delegate pending request check
        $this->membershipService->ensureNoPendingJoinRequest($club, $user);

        // Delegate join request creation
        $request = $this->membershipService->createJoinRequest($club, $user, $description);

        // Delegate audit logging
        $this->auditService->log($club, 'request_join', $user, null, ['request_id' => $request->id]);

        return $request;
    }

    /**
     * Approve a join request and add the user as a member.
     * 
     * @param Club $club The club
     * @param User $user The user whose request to approve
     * @param User|null $approver The user approving the request
     * @return bool True if approved successfully
     */
    public function approveJoin(Club $club, User $user, ?User $approver = null): bool
    {
        // Delegate approver validation
        $this->authorizationService->ensureApproverProvided($approver);
        $this->authorizationService->ensureCanApproveJoin($approver);

        // Delegate membership check
        $this->membershipService->ensureNotAlreadyMember($club, $user);

        // Delegate join request approval
        $this->membershipService->approveJoinRequest($club, $user);

        // Delegate member addition
        $this->membershipService->addMember($club, $user);

        // Delegate audit logging
        $this->auditService->log($club, 'approve_join', $approver, $user);

        return true;
    }

    /**
     * Reject a join request.
     * 
     * @param Club $club The club
     * @param User $user The user whose request to reject
     * @param User|null $rejector The user rejecting the request
     * @return bool True if rejected successfully
     */
    public function rejectJoin(Club $club, User $user, ?User $rejector = null): bool
    {
        // Delegate rejector validation
        $this->authorizationService->ensureApproverProvided($rejector);
        $this->authorizationService->ensureCanRejectJoin($rejector);

        // Delegate join request rejection
        $this->membershipService->rejectJoinRequest($club, $user);

        // Delegate audit logging
        $this->auditService->log($club, 'reject_join', $rejector, $user);

        return true;
    }

    // ============================================
    // Query/Helper Methods (Read-only operations)
    // ============================================

    /**
     * Get club for a club account user.
     * 
     * @param User $clubAccount The club account user
     * @return Club|null
     */
    public function getClubForAccount(User $clubAccount): ?Club
    {
        return $this->clubService->getClubForAccount($clubAccount);
    }

    /**
     * Update club profile.
     * 
     * @param Club $club The club to update
     * @param array $data Updated data
     * @param User $updater The user updating the club
     * @return Club
     */
    public function updateClubProfile(Club $club, array $data, User $updater): Club
    {
        // Delegate club update
        $club = $this->clubService->update($club, $data);

        // Delegate audit logging
        $this->auditService->log($club, 'update_club_profile', $updater, null, [
            'updated_fields' => array_keys($data),
        ]);

        return $club;
    }

    /**
     * List all members of a club.
     * 
     * @param Club $club The club
     * @return Collection|User[]
     */
    public function listMembers(Club $club): Collection
    {
        return $this->membershipService->listMembers($club);
    }

    /**
     * Check if a club is active.
     * 
     * @param Club $club The club
     * @return bool
     */
    public function isActive(Club $club): bool
    {
        return $this->clubService->isActive($club);
    }

    /**
     * Check if a user is a member of a club.
     * 
     * @param Club $club The club
     * @param User $user The user to check
     * @return bool
     */
    public function hasMember(Club $club, User $user): bool
    {
        return $this->membershipService->hasMember($club, $user);
    }

    /**
     * Get the role of a member in a club.
     * 
     * @param Club $club The club
     * @param User $user The member user
     * @return string|null
     */
    public function getMemberRole(Club $club, User $user): ?string
    {
        return $this->membershipService->getMemberRole($club, $user);
    }

    /**
     * Count the number of members in a club.
     * 
     * @param Club $club The club
     * @return int
     */
    public function countMembers(Club $club): int
    {
        return $this->membershipService->countMembers($club);
    }

    /**
     * Get clubs pending approval.
     * 
     * @param array $filters Optional filters
     * @return Collection|Club[]
     */
    public function getPendingClubs(array $filters = []): Collection
    {
        return $this->clubService->getPendingClubs($filters);
    }

    /**
     * Get active clubs with statistics.
     * 
     * @param array $filters Optional filters
     * @return Collection|Club[]
     */
    public function getActiveClubsWithStats(array $filters = []): Collection
    {
        return $this->clubService->getActiveClubsWithStats($filters);
    }

    /**
     * Check if a club slug is available.
     * 
     * @param string $slug The slug to check
     * @param Club|null $excludeClub Club to exclude from uniqueness check
     * @return bool
     */
    public function isSlugAvailable(string $slug, ?Club $club = null): bool
    {
        return $this->clubService->isSlugAvailable($slug, $club);
    }

    // ============================================
    // Legacy/Unimplemented Methods
    // ============================================

    /**
     * Transfer club ownership/administration to another user.
     * 
     * @param Club $club The club to transfer
     * @param User $newOwner The new owner/administrator
     * @param User $transferredBy The user performing the transfer
     * @return Club
     * @throws \Exception If transfer is not allowed
     */
    public function transferOwnership(Club $club, User $newOwner, User $transferredBy): Club
    {
        // TODO: Implement in ClubService
        throw new \Exception("Method not yet implemented.");
    }

    /**
     * Check if a user can perform an action on a club.
     * 
     * @param User $user The user to check
     * @param Club $club The club
     * @param string $action The action to check
     * @return bool
     */
    public function canPerformAction(User $user, Club $club, string $action): bool
    {
        // TODO: Implement in ClubAuthorizationService
        return false;
    }

    /**
     * Get clubs that a user can manage.
     * 
     * @param User $user The user
     * @return Collection|Club[]
     */
    public function getManageableClubs(User $user): Collection
    {
        // TODO: Implement in ClubService
        return collect();
    }

    /**
     * Bulk update club statuses.
     * 
     * @param array $clubIds Array of club IDs
     * @param string $status New status to set
     * @param User|null $updatedBy User performing the bulk update
     * @return int Number of clubs updated
     */
    public function bulkUpdateStatus(array $clubIds, string $status, ?User $updatedBy = null): int
    {
        // TODO: Implement in ClubService
        return 0;
    }

    /**
     * Get club statistics and analytics.
     * 
     * @param Club $club The club
     * @return array Statistics array
     */
    public function getClubStatistics(Club $club): array
    {
        // TODO: Implement in ClubService
        return [];
    }

    /**
     * Validate club data before creation or update.
     * 
     * @param array $data Club data to validate
     * @param Club|null $club Existing club (for updates)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateClubData(array $data, ?Club $club = null): array
    {
        // TODO: Implement validation service
        return ['valid' => true, 'errors' => []];
    }

    // ============================================
    // Blacklist Management Methods
    // ============================================

    /**
     * Add a user to club blacklist.
     * 
     * @param Club $club The club
     * @param User $user The user to blacklist
     * @param string|null $reason Optional reason for blacklisting
     * @param User|null $blacklistedBy The user performing the blacklist action
     * @return \App\Models\ClubBlacklist The created blacklist entry
     */
    public function addToBlacklist(Club $club, User $user, ?string $reason = null, ?User $blacklistedBy = null): \App\Models\ClubBlacklist
    {
        $actor = $blacklistedBy ?? auth()->user() ?? $club->creator;
        
        // Check if user is a member before blacklisting (for audit logging)
        $wasMember = $this->membershipService->hasMember($club, $user);
        
        // Delegate to membership service (this will remove member if they are one)
        $blacklist = $this->membershipService->addToBlacklist($club, $user, $reason, $actor);

        // Delegate audit logging
        $this->auditService->log($club, 'add_to_blacklist', $actor, $user, [
            'reason' => $reason,
            'was_member' => $wasMember,
        ]);
        
        // If user was a member, also log the removal
        if ($wasMember) {
            $this->auditService->log($club, 'remove_member', $actor, $user, [
                'reason' => 'Removed due to blacklisting',
            ]);
        }

        return $blacklist;
    }

    /**
     * Remove a user from club blacklist.
     * 
     * @param Club $club The club
     * @param User $user The user to remove from blacklist
     * @return bool True if removed successfully
     */
    public function removeFromBlacklist(Club $club, User $user): bool
    {
        // Delegate to membership service
        $this->membershipService->removeFromBlacklist($club, $user);

        // Delegate audit logging
        $actor = auth()->user() ?? $club->creator;
        $this->auditService->log($club, 'remove_from_blacklist', $actor, $user);

        return true;
    }

    /**
     * Clear cooldown period for a removed member.
     * This allows the user to immediately request to join again.
     * 
     * @param Club $club The club
     * @param User $user The user whose cooldown to clear
     * @param User|null $clearedBy The user clearing the cooldown
     * @return bool True if cooldown was cleared
     */
    public function clearMemberCooldown(Club $club, User $user, ?User $clearedBy = null): bool
    {
        // Delegate to membership service
        $this->membershipService->clearMemberCooldown($club, $user);

        // Delegate audit logging
        $actor = $clearedBy ?? auth()->user() ?? $club->creator;
        $this->auditService->log($club, 'clear_member_cooldown', $actor, $user);

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
        return $this->membershipService->isBlacklisted($club, $user);
    }

    // ============================================
    // Announcement Management Methods
    // ============================================

    /**
     * Create a new announcement for a club.
     * 
     * Facade method: Coordinates authorization, creation, and audit logging.
     * 
     * @param Club $club The club
     * @param array $data Announcement data
     * @param User $creator The user creating the announcement
     * @return \App\Models\ClubAnnouncement
     * @throws \Exception If creation fails
     */
    public function createAnnouncement(Club $club, array $data, User $creator): \App\Models\ClubAnnouncement
    {
        // Delegate authorization check
        $this->authorizationService->ensureCanManageAnnouncements($creator, $club);

        // Delegate announcement creation
        $announcement = $this->announcementService->create($club, $data, $creator);

        // Delegate audit logging
        $this->auditService->log($club, 'create_announcement', $creator, null, [
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
        ]);

        return $announcement;
    }

    /**
     * Update an announcement.
     * 
     * @param Club $club The club
     * @param \App\Models\ClubAnnouncement $announcement The announcement to update
     * @param array $data Updated data
     * @param User $updater The user updating the announcement
     * @return \App\Models\ClubAnnouncement
     */
    public function updateAnnouncement(Club $club, \App\Models\ClubAnnouncement $announcement, array $data, User $updater): \App\Models\ClubAnnouncement
    {
        // Delegate authorization check
        $this->authorizationService->ensureCanManageAnnouncements($updater, $club);

        // Ensure announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            throw new \Exception("Announcement does not belong to this club.");
        }

        // Delegate announcement update
        $announcement = $this->announcementService->update($announcement, $data);

        // Delegate audit logging
        $this->auditService->log($club, 'update_announcement', $updater, null, [
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
        ]);

        return $announcement;
    }

    /**
     * Delete an announcement.
     * 
     * @param Club $club The club
     * @param \App\Models\ClubAnnouncement $announcement The announcement to delete
     * @param User $deleter The user deleting the announcement
     * @return bool
     */
    public function deleteAnnouncement(Club $club, \App\Models\ClubAnnouncement $announcement, User $deleter): bool
    {
        // Delegate authorization check
        $this->authorizationService->ensureCanManageAnnouncements($deleter, $club);

        // Ensure announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            throw new \Exception("Announcement does not belong to this club.");
        }

        $announcementId = $announcement->id;
        $announcementTitle = $announcement->title;

        // Delegate announcement deletion
        $result = $this->announcementService->delete($announcement);

        // Delegate audit logging
        if ($result) {
            $this->auditService->log($club, 'delete_announcement', $deleter, null, [
                'announcement_id' => $announcementId,
                'title' => $announcementTitle,
            ]);
        }

        return $result;
    }

    /**
     * Publish an announcement.
     * 
     * @param Club $club The club
     * @param \App\Models\ClubAnnouncement $announcement The announcement to publish
     * @param User $publisher The user publishing the announcement
     * @return \App\Models\ClubAnnouncement
     */
    public function publishAnnouncement(Club $club, \App\Models\ClubAnnouncement $announcement, User $publisher): \App\Models\ClubAnnouncement
    {
        // Delegate authorization check
        $this->authorizationService->ensureCanManageAnnouncements($publisher, $club);

        // Ensure announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            throw new \Exception("Announcement does not belong to this club.");
        }

        // Delegate announcement publishing
        $announcement = $this->announcementService->publish($announcement);

        // Delegate audit logging
        $this->auditService->log($club, 'publish_announcement', $publisher, null, [
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
        ]);

        return $announcement;
    }

    /**
     * Unpublish an announcement.
     * 
     * @param Club $club The club
     * @param \App\Models\ClubAnnouncement $announcement The announcement to unpublish
     * @param User $unpublisher The user unpublishing the announcement
     * @return \App\Models\ClubAnnouncement
     */
    public function unpublishAnnouncement(Club $club, \App\Models\ClubAnnouncement $announcement, User $unpublisher): \App\Models\ClubAnnouncement
    {
        // Delegate authorization check
        $this->authorizationService->ensureCanManageAnnouncements($unpublisher, $club);

        // Ensure announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            throw new \Exception("Announcement does not belong to this club.");
        }

        // Delegate announcement unpublishing
        $announcement = $this->announcementService->unpublish($announcement);

        // Delegate audit logging
        $this->auditService->log($club, 'unpublish_announcement', $unpublisher, null, [
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
        ]);

        return $announcement;
    }

    /**
     * Get announcements for a club.
     * 
     * @param Club $club The club
     * @param array $filters Optional filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAnnouncements(Club $club, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->announcementService->getAnnouncements($club, $filters);
    }

    /**
     * Get a single announcement.
     * 
     * @param Club $club The club
     * @param int $announcementId The announcement ID
     * @return \App\Models\ClubAnnouncement|null
     */
    public function getAnnouncement(Club $club, int $announcementId): ?\App\Models\ClubAnnouncement
    {
        return $this->announcementService->getAnnouncement($club, $announcementId);
    }

    // ============================================
    // Logging Helper Methods (for GET requests)
    // ============================================

    /**
     * Log a club view action (for GET requests).
     * 
     * @param Club $club The club being viewed
     * @param User $viewer The user viewing the club
     * @param string|null $requestId Optional request ID for tracking
     * @return void
     */
    public function logClubView(Club $club, User $viewer, ?string $requestId = null): void
    {
        $this->auditService->log($club, 'view_club', $viewer, null, [], $requestId);
    }

    /**
     * Log a user clubs list view (for GET requests).
     * 
     * @param User $user The user whose clubs are being viewed
     * @param User $viewer The user viewing the clubs list
     * @param string|null $requestId Optional request ID for tracking
     * @return void
     */
    public function logUserClubsView(User $user, User $viewer, ?string $requestId = null): void
    {
        // Get the first club for logging (or create a system club if user has no clubs)
        // Since this is a user-level action, we might not have a specific club
        // Option 1: Log to a system club (if exists)
        // Option 2: Skip logging for this action
        // Option 3: Create a special log entry without club_id
        
        // For now, we'll skip logging this action as it's user-level, not club-level
        // If needed, you can create a system club or modify the log structure
    }
}
