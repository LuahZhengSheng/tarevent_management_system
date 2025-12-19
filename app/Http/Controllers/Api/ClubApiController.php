<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubFacade;
use Illuminate\Http\Request;

class ClubApiController extends Controller
{
    /**
     * Create a new club.
     *
     * @param Request $request
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, ClubFacade $facade)
    {
        // Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'club_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        // Handle logo file upload if present
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoPath = $logoFile->store('clubs/logos', 'public');
            $validated['logo'] = $logoPath;
        }

        // Pass validated data to ClubFacade
        $club = $facade->createClub($validated, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Club created successfully.',
            'data' => $club,
        ], 201);
    }

    /**
     * Update a club.
     *
     * @param Request $request
     * @param Club $club
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Club $club)
    {
        // Validate input
        $validated = $request->validate([
            'club_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        // Update club
        $club->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Club updated successfully.',
            'data' => $club->fresh(),
        ], 200);
    }

    /**
     * Request to join a club.
     *
     * @param Request $request
     * @param Club $club
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestJoin(Request $request, Club $club, ClubFacade $facade)
    {
        try {
            // Validate optional parameters from modal
            $validated = $request->validate([
                'reason' => ['nullable', 'string', 'max:500'],
                'agree' => ['required', 'boolean'],
            ]);

            // Map 'reason' from modal to 'description' for database
            $description = $validated['reason'] ?? null;

            $joinRequest = $facade->requestJoin($club, auth()->user(), $description);

            return response()->json([
                'success' => true,
                'message' => 'Join request submitted successfully.',
                'data' => [
                    'id' => $joinRequest->id,
                    'club_id' => $joinRequest->club_id,
                    'user_id' => $joinRequest->user_id,
                    'status' => $joinRequest->status,
                    'description' => $joinRequest->description,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Approve a join request.
     *
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveJoin(Club $club, User $user, ClubFacade $facade)
    {
        $facade->approveJoin($club, $user, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Join request approved successfully.',
        ], 200);
    }

    /**
     * Reject a join request.
     *
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectJoin(Club $club, User $user, ClubFacade $facade)
    {
        $facade->rejectJoin($club, $user, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Join request rejected successfully.',
        ], 200);
    }

    /**
     * Get all clubs for a specific user.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserClubs(User $user)
    {
        $clubs = $user->clubs()
            ->with(['creator', 'clubUser'])
            ->get()
            ->map(function ($club) use ($user) {
                return [
                    'id' => $club->id,
                    'name' => $club->name,
                    'slug' => $club->slug,
                    'description' => $club->description,
                    'email' => $club->email,
                    'phone' => $club->phone,
                    'logo' => $club->logo,
                    'status' => $club->status,
                    'member_role' => $club->pivot->role ?? null,
                    'joined_at' => $club->pivot->created_at ?? null,
                    'creator' => $club->creator ? [
                        'id' => $club->creator->id,
                        'name' => $club->creator->name,
                    ] : null,
                    'club_user' => $club->clubUser ? [
                        'id' => $club->clubUser->id,
                        'name' => $club->clubUser->name,
                        'email' => $club->clubUser->email,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Clubs retrieved successfully.',
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_clubs' => $clubs->count(),
                'clubs' => $clubs,
            ],
        ], 200);
    }
}

