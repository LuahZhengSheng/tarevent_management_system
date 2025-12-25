<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubFacade;

class ClubController extends Controller
{
    /**
     * Store a newly created club.
     */
    public function store(Request $request, ClubFacade $facade)
    {
        $club = $facade->createClub($request->all(), auth()->user());

        return redirect()->back()
            ->with('success', 'Club created successfully.');
    }

    // Removed: Approve/Reject club creation actions
    // Admin-created clubs are now immediately active and approved during creation.
    // 
    // /**
    //  * Approve a club.
    //  */
    // public function approve(Club $club, ClubFacade $facade)
    // {
    //     $facade->approveClub($club, auth()->user(), true);
    //
    //     return redirect()->back()
    //         ->with('success', 'Club approved successfully.');
    // }
    //
    // /**
    //  * Reject a club.
    //  */
    // public function reject(Request $request, Club $club, ClubFacade $facade)
    // {
    //     $facade->rejectClub($club, auth()->user(), $request->input('reason'));
    //
    //     return redirect()->back()
    //         ->with('success', 'Club rejected successfully.');
    // }

    /**
     * Activate a club.
     */
    public function activate(Club $club, ClubFacade $facade)
    {
        $facade->activateClub($club, auth()->user());

        return redirect()->back()
            ->with('success', 'Club activated successfully.');
    }

    /**
     * Deactivate a club.
     */
    public function deactivate(Request $request, Club $club, ClubFacade $facade)
    {
        $facade->deactivateClub($club, auth()->user(), $request->input('reason'));

        return redirect()->back()
            ->with('success', 'Club deactivated successfully.');
    }

    /**
     * Show current club for club account.
     */
    public function show(ClubFacade $facade)
    {
        $club = $facade->getClubForAccount(auth()->user());

        if (!$club) {
            abort(404, 'Club not found for this account.');
        }

        return view('clubs.show', compact('club'));
    }

    /**
     * List members of a club.
     */
    public function members(Club $club, ClubFacade $facade)
    {
        $members = $facade->listMembers($club);

        return view('clubs.members', compact('club', 'members'));
    }

    /**
     * Add a member to a club.
     */
    public function addMember(Request $request, Club $club, User $user, ClubFacade $facade)
    {
        $facade->addMember($club, $user, $request->input('role'));

        return redirect()->back()
            ->with('success', 'Member added successfully.');
    }

    /**
     * Update a member's role in a club.
     */
    public function updateMemberRole(Request $request, Club $club, User $user, ClubFacade $facade)
    {
        $facade->updateMemberRole($club, $user, $request->input('role'), auth()->user());

        return redirect()->back()
            ->with('success', 'Member role updated successfully.');
    }

    /**
     * Remove a member from a club.
     */
    public function removeMember(Club $club, User $user, ClubFacade $facade)
    {
        $facade->removeMember($club, $user, auth()->user());

        return redirect()->back()
            ->with('success', 'Member removed successfully.');
    }

    /**
     * Transfer club ownership.
     */
    public function transferOwnership(Request $request, Club $club, ClubFacade $facade)
    {
        $newOwner = User::findOrFail($request->input('new_owner_id'));
        $facade->transferOwnership($club, $newOwner, auth()->user());

        return redirect()->back()
            ->with('success', 'Club ownership transferred successfully.');
    }

    /**
     * Bulk update club statuses.
     */
    public function bulkUpdateStatus(Request $request, ClubFacade $facade)
    {
        $facade->bulkUpdateStatus($request->input('club_ids', []), $request->input('status'), auth()->user());

        return redirect()->back()
            ->with('success', 'Club statuses updated successfully.');
    }

    /**
     * Request to join a club.
     */
    public function requestJoin(Club $club, ClubFacade $facade)
    {
        $facade->requestJoin($club, auth()->user());

        return redirect()->back()
            ->with('success', 'Join request submitted successfully.');
    }

    /**
     * Approve a join request.
     */
    public function approveJoin(Club $club, User $user, ClubFacade $facade)
    {
        $facade->approveJoin($club, $user, auth()->user());

        return redirect()->back()
            ->with('success', 'Join request approved successfully.');
    }

    /**
     * Reject a join request.
     */
    public function rejectJoin(Club $club, User $user, ClubFacade $facade)
    {
        $facade->rejectJoin($club, $user, auth()->user());

        return redirect()->back()
            ->with('success', 'Join request rejected successfully.');
    }
}
