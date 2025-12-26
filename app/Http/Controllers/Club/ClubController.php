<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubLog;
use App\Models\User;
use App\Services\ClubFacade;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Unified ClubController
 *
 * Handles both Admin and Club-side operations:
 * - Admin operations: index, create, store, show, activate, deactivate, logs
 * - Club-side operations: show (own club), members, member management, join requests
 */
class ClubController extends Controller
{
    public function __construct(
        private ClubFacade $clubFacade
    ) {}

    // ==========================================
    // ADMIN OPERATIONS
    // ==========================================

    /**
     * Display a listing of clubs (Admin only).
     */
    public function adminIndex(Request $request): View|JsonResponse
    {
        $query = Club::with(['creator', 'clubUser'])->select('clubs.*');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->input('per_page', 15);
        $clubs = $query->paginate($perPage)->withQueryString();

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.clubs.partials.club-table', compact('clubs'))->render(),
                'pagination' => view('admin.clubs.partials.pagination', compact('clubs'))->render(),
            ]);
        }

        return view('admin.clubs.index', compact('clubs'));
    }

    /**
     * Show the form for creating a new club (Admin only).
     */
    public function adminCreate(): View
    {
        return view('admin.clubs.create');
    }

    /**
     * Check if user email already exists.
     * Used for real-time validation in admin create club form.
     * This method checks directly against the User model without modifying UserService.
     */
    public function checkUserEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = $request->input('email');

        // Check if email exists in users table
        // We use User model directly to check without modifying UserService
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This email is already registered to a user' : 'Email is available',
        ]);
    }

    /**
     * Store a newly created club (Admin only).
     */
    public function adminStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100', 'in:academic,sports,cultural,social,volunteer,professional,other'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:clubs,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Handle logo file upload if present
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoPath = $logoFile->store('clubs/logos', 'public');
            $validated['logo'] = $logoPath;
        }

        // Create club with admin as creator
        $club = $this->clubFacade->createClub($validated, auth()->user());

        // Approve and activate the club immediately (admin-created clubs are auto-approved)
        $club = $this->clubFacade->approveClub($club, auth()->user(), true);

        return redirect()->route('admin.clubs.show', $club)
            ->with('success', 'Club created and activated successfully.');
    }

    /**
     * Display the specified club (Admin view).
     */
    public function adminShow(Club $club): View
    {
        $club->load(['creator', 'clubUser', 'members']);

        // Get club statistics
        $stats = [
            'total_members' => $club->members()->wherePivot('status', 'active')->count(),
            'pending_requests' => $club->joinRequests()->where('status', 'pending')->count(),
            'total_announcements' => $club->announcements()->count(),
            'published_announcements' => $club->announcements()->where('status', 'published')->count(),
        ];

        return view('admin.clubs.show', compact('club', 'stats'));
    }

    /**
     * Activate a club (Admin only).
     */
    public function adminActivate(Club $club): RedirectResponse
    {
        try {
            $this->clubFacade->activateClub($club, auth()->user());
            return redirect()->back()->with('success', 'Club activated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Deactivate a club (Admin only).
     */
    public function adminDeactivate(Request $request, Club $club): RedirectResponse
    {
        try {
            $reason = $request->input('reason');
            $this->clubFacade->deactivateClub($club, auth()->user(), $reason);
            return redirect()->back()->with('success', 'Club deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display club logs (Admin only).
     */
    public function adminLogs(Club $club, Request $request): View|JsonResponse
    {
        $query = ClubLog::where('club_id', $club->id)
            ->with(['actor', 'targetUser'])
            ->orderBy('created_at', 'desc');

        // Filter by action if provided
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter by actor if provided
        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->input('actor_id'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Paginate
        $perPage = $request->input('per_page', 20);
        $logs = $query->paginate($perPage)->withQueryString();

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.clubs.partials.log-table', compact('logs'))->render(),
                'pagination' => view('admin.clubs.partials.log-pagination', compact('logs'))->render(),
            ]);
        }

        return view('admin.clubs.logs', compact('club', 'logs'));
    }

    // ==========================================
    // CLUB-SIDE OPERATIONS
    // ==========================================

    /**
     * Store a newly created club (Legacy - used by admin create form).
     */
    public function store(Request $request): RedirectResponse
    {
        $club = $this->clubFacade->createClub($request->all(), auth()->user());

        return redirect()->back()
            ->with('success', 'Club created successfully.');
    }

    /**
     * Club Dashboard - Main landing page for club account.
     */
    public function dashboard(): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Get statistics
        $stats = [
            'total_members' => $club->members()->wherePivot('status', 'active')->count(),
            'pending_requests' => $club->joinRequests()->where('status', 'pending')->count(),
            'total_announcements' => $club->announcements()->count(),
            'total_events' => 0, // Reserved for Event Module
        ];

        // Get recent activity logs (last 10)
        $recentLogs = $club->logs()
            ->with(['actor', 'targetUser'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('clubs.dashboard', compact('club', 'stats', 'recentLogs'));
    }

    /**
     * Activate a club (Club-side).
     */
    public function activate(Club $club): RedirectResponse
    {
        $this->clubFacade->activateClub($club, auth()->user());

        return redirect()->back()
            ->with('success', 'Club activated successfully.');
    }

    /**
     * Deactivate a club (Club-side).
     */
    public function deactivate(Request $request, Club $club): RedirectResponse
    {
        $this->clubFacade->deactivateClub($club, auth()->user(), $request->input('reason'));

        return redirect()->back()
            ->with('success', 'Club deactivated successfully.');
    }

    /**
     * List members of a club (Club-side).
     */
    public function membersIndex(): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Get active members
        $activeMembers = $club->members()
            ->wherePivot('status', 'active')
            ->withPivot('role', 'status', 'created_at')
            ->orderBy('club_user.created_at', 'desc')
            ->get();

        // Get removed members (include updated_at for cooldown calculation)
        $removedMembers = $club->members()
            ->wherePivot('status', 'removed')
            ->withPivot('role', 'status', 'created_at', 'updated_at')
            ->orderBy('club_user.updated_at', 'desc')
            ->get();

        // Get blacklisted users
        $blacklistedUsers = $club->blacklistedUsers()
            ->withPivot('reason', 'blacklisted_by', 'created_at')
            ->orderBy('club_blacklist.created_at', 'desc')
            ->get();

        return view('clubs.members.index', compact('club', 'activeMembers', 'removedMembers', 'blacklistedUsers'));
    }

    /**
     * Add a member to a club.
     */
    public function addMember(Request $request, Club $club, User $user): RedirectResponse
    {
        $this->clubFacade->addMember($club, $user, $request->input('role'));

        return redirect()->back()
            ->with('success', 'Member added successfully.');
    }

    /**
     * Update a member's role in a club.
     */
    public function updateMemberRole(Request $request, User $user)
    {
        // Get club from current club account (not from route parameter)
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        try {
            $this->clubFacade->updateMemberRole($club, $user, $request->input('role'), auth()->user());

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member role updated successfully.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->back()
                ->with('success', 'Member role updated successfully.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a member from a club.
     */
    public function removeMember(Request $request, User $user)
    {
        // Get club from current club account (not from route parameter)
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        try {
            $this->clubFacade->removeMember($club, $user, auth()->user());

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member removed successfully.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->back()
                ->with('success', 'Member removed successfully.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Add a user to blacklist (Club-side).
     */
    public function addToBlacklist(Request $request, User $user)
    {
        // Get club from current club account (not from route parameter)
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        try {
            $reason = $request->input('reason');

            $this->clubFacade->addToBlacklist($club, $user, $reason, auth()->user());

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User added to blacklist successfully.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->back()
                ->with('success', 'User added to blacklist successfully.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a user from blacklist (Club-side).
     */
    public function removeFromBlacklist(Request $request, User $user)
    {
        // Get club from current club account (not from route parameter)
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        try {
            $this->clubFacade->removeFromBlacklist($club, $user);

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User removed from blacklist successfully.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->back()
                ->with('success', 'User removed from blacklist successfully.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Transfer club ownership.
     */
    public function transferOwnership(Request $request, Club $club): RedirectResponse
    {
        $newOwner = User::findOrFail($request->input('new_owner_id'));
        $this->clubFacade->transferOwnership($club, $newOwner, auth()->user());

        return redirect()->back()
            ->with('success', 'Club ownership transferred successfully.');
    }

    /**
     * Bulk update club statuses.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $this->clubFacade->bulkUpdateStatus($request->input('club_ids', []), $request->input('status'), auth()->user());

        return redirect()->back()
            ->with('success', 'Club statuses updated successfully.');
    }

    /**
     * List join requests (Club-side).
     */
    public function joinRequestsIndex(): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Get pending join requests
        $pendingRequests = $club->joinRequests()
            ->where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get approved join requests (recent)
        $approvedRequests = $club->joinRequests()
            ->where('status', 'approved')
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        // Get rejected join requests (recent)
        $rejectedRequests = $club->joinRequests()
            ->where('status', 'rejected')
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('clubs.join-requests.index', compact('club', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Approve a join request (Club-side).
     */
    public function approveJoin(User $user): RedirectResponse
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        $this->clubFacade->approveJoin($club, $user, auth()->user());

        return redirect()->back()
            ->with('success', 'Join request approved successfully.');
    }

    /**
     * Reject a join request (Club-side).
     */
    public function rejectJoin(Request $request, User $user): RedirectResponse
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        $reason = $request->input('reason');

        $this->clubFacade->rejectJoin($club, $user, auth()->user(), $reason);

        return redirect()->back()
            ->with('success', 'Join request rejected successfully.');
    }

    /**
     * Clear cooldown period for a removed member (Club-side).
     */
    public function clearMemberCooldown(Request $request, User $user)
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        try {
            $this->clubFacade->clearMemberCooldown($club, $user, auth()->user());

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member cooldown cleared successfully. User can now request to join again.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->back()
                ->with('success', 'Member cooldown cleared successfully. User can now request to join again.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * List announcements (Club-side).
     */
    public function announcementsIndex(): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Get all announcements
        $announcements = $club->announcements()
            ->with('creator')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Separate published and draft
        $publishedAnnouncements = $announcements->where('status', 'published');
        $draftAnnouncements = $announcements->where('status', 'draft');

        return view('clubs.announcements.index', compact('club', 'publishedAnnouncements', 'draftAnnouncements'));
    }

    /**
     * Show create announcement form (Club-side).
     */
    public function createAnnouncement(): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        return view('clubs.announcements.create', compact('club'));
    }

    /**
     * Store a new announcement (Club-side).
     */
    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status' => ['nullable', 'string', 'in:draft,published'],
        ]);

        // Handle image upload - MUST be done before validation data is passed to service
        // Remove 'image' from validated array to avoid passing file object
        unset($validated['image']);

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imagePath = $imageFile->store('clubs/announcements', 'public');
            $validated['image'] = $imagePath;
        }

        $this->clubFacade->createAnnouncement($club, $validated, auth()->user());

        return redirect()->route('club.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Show edit announcement form (Club-side).
     */
    public function editAnnouncement(\App\Models\ClubAnnouncement $announcement): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Verify announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        return view('clubs.announcements.edit', compact('club', 'announcement'));
    }

    /**
     * Update an announcement (Club-side).
     */
    public function updateAnnouncement(Request $request, \App\Models\ClubAnnouncement $announcement): RedirectResponse
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Verify announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status' => ['nullable', 'string', 'in:draft,published'],
        ]);

        // Handle image upload - MUST be done before validation data is passed to service
        // Remove 'image' from validated array to avoid passing file object
        unset($validated['image']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($announcement->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->image);
            }
            $imageFile = $request->file('image');
            $imagePath = $imageFile->store('clubs/announcements', 'public');
            $validated['image'] = $imagePath;
        } elseif ($request->has('remove_image') && $request->input('remove_image') === '1') {
            // Remove image if requested
            if ($announcement->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->image);
            }
            $validated['image'] = null;
        }

        $this->clubFacade->updateAnnouncement($club, $announcement, $validated, auth()->user());

        return redirect()->route('club.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Delete an announcement (Club-side).
     */
    public function deleteAnnouncement(Request $request, \App\Models\ClubAnnouncement $announcement)
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Verify announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        try {
            $this->clubFacade->deleteAnnouncement($club, $announcement, auth()->user());

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Announcement deleted successfully.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('club.announcements.index')
                ->with('success', 'Announcement deleted successfully.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Publish an announcement (Club-side).
     */
    public function publishAnnouncement(Request $request, \App\Models\ClubAnnouncement $announcement)
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Verify announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        try {
            $this->clubFacade->publishAnnouncement($club, $announcement, auth()->user());

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Announcement published successfully.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('club.announcements.index')
                ->with('success', 'Announcement published successfully.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Unpublish an announcement (Club-side).
     */
    public function unpublishAnnouncement(Request $request, \App\Models\ClubAnnouncement $announcement)
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        // Verify announcement belongs to club
        if ($announcement->club_id !== $club->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        try {
            $this->clubFacade->unpublishAnnouncement($club, $announcement, auth()->user());

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Announcement unpublished successfully.',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('club.announcements.index')
                ->with('success', 'Announcement unpublished successfully.');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            // Return redirect with error for regular form submissions
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * View activity logs (Club-side).
     */
    public function logsIndex(Request $request): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        $query = $club->logs()
            ->with(['actor', 'targetUser'])
            ->orderBy('created_at', 'desc');

        // Filter by action if provided
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Paginate
        $perPage = $request->input('per_page', 20);
        $logs = $query->paginate($perPage)->withQueryString();

        return view('clubs.logs.index', compact('club', 'logs'));
    }

    /**
     * Show edit club profile form (Club-side).
     */
    public function editProfile(): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        return view('clubs.profile.edit', compact('club'));
    }

    /**
     * Update club profile (Club-side).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100', 'in:academic,sports,cultural,social,volunteer,professional,other'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:clubs,email,' . $club->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_background_image' => ['nullable', 'boolean'],
        ]);

        // Handle logo upload/removal
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($club->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($club->logo);
            }
            $validated['logo'] = $request->file('logo')->store('clubs/logos', 'public');
        } elseif ($request->input('remove_logo')) {
            if ($club->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($club->logo);
            }
            $validated['logo'] = null;
        }

        // Handle background image upload/removal
        if ($request->hasFile('background_image')) {
            // Delete old background image if exists
            if ($club->background_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($club->background_image);
            }
            $validated['background_image'] = $request->file('background_image')->store('clubs/backgrounds', 'public');
        } elseif ($request->input('remove_background_image')) {
            if ($club->background_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($club->background_image);
            }
            $validated['background_image'] = null;
        }

        $this->clubFacade->updateClubProfile($club, $validated, auth()->user());

        return redirect()->route('club.profile.edit')
            ->with('success', 'Club profile updated successfully.');
    }

    /**
     * Show forum page for club (Club-side).
     * Displays the club's forum posts feed.
     */
    public function forumIndex(): View
    {
        $club = $this->clubFacade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        return view('clubs.forum.index', compact('club'));
    }

    // ==========================================
    // STUDENT-SIDE OPERATIONS (Public Browsing)
    // ==========================================

    /**
     * Display a listing of all available clubs (Student-side).
     * Similar to Events index page.
     */
    public function index(Request $request): View
    {
        // Get categories for filter
        $categories = ['academic', 'sports', 'cultural', 'social', 'volunteer', 'professional', 'other'];

        // Get search and filter parameters
        $search = $request->input('search', '');
        $category = $request->input('category', '');

        // This will be populated by JavaScript via API
        // We just prepare the view with filters
        return view('clubs.index', compact('categories', 'search', 'category'));
    }

    /**
     * Display a single club's details (Student-side).
     */
    public function show(Club $club): View
    {
        // Check if club is active (public should only see active clubs)
        if ($club->status !== 'active') {
            abort(404, 'Club not found.');
        }

        // Get membership status and join status if user is authenticated
        $isMember = false;
        $memberRole = null;
        $joinStatus = null;
        if (auth()->check()) {
            $user = auth()->user();
            $isMember = $this->clubFacade->hasMember($club, $user);
            if ($isMember) {
                $memberRole = $this->clubFacade->getMemberRole($club, $user);
            } else {
                // Get join status for non-members (pending, rejected, etc.)
                $membershipService = app(\App\Services\Club\MembershipService::class);
                $joinStatus = $membershipService->getClubJoinStatus($club, $user);
            }
        }

        // Get club members with roles (excluding regular 'member' role)
        // Show leadership roles: president, vice_president, secretary, treasurer, officer, committee_member
        $leadershipMembers = $club->members()
            ->wherePivot('status', 'active')
            ->wherePivotIn('role', ['president', 'vice_president', 'secretary', 'treasurer', 'officer', 'committee_member'])
            ->orderByRaw("FIELD(club_user.role, 'president', 'vice_president', 'secretary', 'treasurer', 'officer', 'committee_member')")
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->pivot->role,
                    'role_display' => ucfirst(str_replace('_', ' ', $member->pivot->role)),
                ];
            });

        // Get statistics
        $stats = [
            'members_count' => $this->clubFacade->countMembers($club),
            'announcements_count' => $club->announcements()->published()->count(),
            'events_count' => 0, // Reserved for Event Module
        ];

        // Get recent announcements (last 5) - only if user is a member
        $recentAnnouncements = collect();
        if ($isMember) {
            $recentAnnouncements = $club->announcements()
                ->published()
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('clubs.show', compact('club', 'isMember', 'memberRole', 'joinStatus', 'leadershipMembers', 'stats', 'recentAnnouncements'));
    }
}
