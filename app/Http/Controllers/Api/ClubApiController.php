<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClubApiController extends Controller
{
    /**
     * Format API response according to IFA standards
     * 
     * @param string $status Status code: S (Success), F (Fail), E (Error)
     * @param array $data Response data
     * @param int $httpStatusCode HTTP status code
     * @param string|null $message Optional message
     * @return JsonResponse
     */
    protected function formatResponse(
        string $status,
        array $data = [],
        int $httpStatusCode = 200,
        ?string $message = null
    ): JsonResponse {
        // IFA standard fields
        $response = [
            'status' => $status,  // IFA standard: S/F/E
            'timestamp' => now()->format('Y-m-d H:i:s'),  // IFA standard: YYYY-MM-DD HH:MM:SS
        ];

        // Backward compatibility: add 'success' field for existing frontend code
        $response['success'] = ($status === 'S');

        if ($message !== null) {
            $response['message'] = $message;
        }

        // Merge data into response
        $response = array_merge($response, $data);

        return response()->json($response, $httpStatusCode);
    }

    /**
     * Format successful response (Status: S)
     * 
     * @param array $data Response data
     * @param string|null $message Optional success message
     * @param int $httpStatusCode HTTP status code (default: 200)
     * @return JsonResponse
     */
    protected function successResponse(
        array $data = [],
        ?string $message = null,
        int $httpStatusCode = 200
    ): JsonResponse {
        return $this->formatResponse('S', $data, $httpStatusCode, $message);
    }

    /**
     * Format failure response (Status: F)
     * 
     * @param string $message Failure message
     * @param array $data Additional data
     * @param int $httpStatusCode HTTP status code (default: 400)
     * @return JsonResponse
     */
    protected function failResponse(
        string $message,
        array $data = [],
        int $httpStatusCode = 400
    ): JsonResponse {
        return $this->formatResponse('F', $data, $httpStatusCode, $message);
    }

    /**
     * Format error response (Status: E)
     * 
     * @param string $message Error message
     * @param array $data Additional data
     * @param int $httpStatusCode HTTP status code (default: 500)
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message,
        array $data = [],
        int $httpStatusCode = 500
    ): JsonResponse {
        return $this->formatResponse('E', $data, $httpStatusCode, $message);
    }
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

        return $this->successResponse([
            'data' => $club,
        ], 'Club created successfully.', 201);
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

        return $this->successResponse([
            'data' => $club->fresh(),
        ], 'Club updated successfully.');
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

            return $this->successResponse([
                'data' => [
                    'id' => $joinRequest->id,
                    'club_id' => $joinRequest->club_id,
                    'user_id' => $joinRequest->user_id,
                    'status' => $joinRequest->status,
                    'description' => $joinRequest->description,
                ],
            ], 'Join request submitted successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
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
        try {
            $facade->approveJoin($club, $user, auth()->user());

            return $this->successResponse([], 'Join request approved successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
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
        try {
            $facade->rejectJoin($club, $user, auth()->user());

            return $this->successResponse([], 'Join request rejected successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
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
                    'is_member' => ($club->pivot->status ?? 'active') === 'active',
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

        return $this->successResponse([
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_clubs' => $clubs->count(),
                'clubs' => $clubs,
            ],
        ], 'Clubs retrieved successfully.');
    }

    /**
     * Get a single club by ID with membership status for the authenticated user.
     *
     * @param Club $club
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Club $club, ClubFacade $facade)
    {
        $user = auth()->user();
        
        if (!$user) {
            return $this->failResponse('Unauthenticated.', [], 401);
        }

        // Check if user is a member (only for student role)
        $isMember = false;
        if ($user->role === 'student') {
            $isMember = $facade->hasMember($club, $user);
        }

        // Load relationships
        $club->load(['creator', 'clubUser']);

        return $this->successResponse([
            'data' => [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'description' => $club->description,
                'email' => $club->email,
                'phone' => $club->phone,
                'logo' => $club->logo ? Storage::url($club->logo) : null,
                'status' => $club->status,
                'is_member' => $isMember,
                'creator' => $club->creator ? [
                    'id' => $club->creator->id,
                    'name' => $club->creator->name,
                ] : null,
                'club_user' => $club->clubUser ? [
                    'id' => $club->clubUser->id,
                    'name' => $club->clubUser->name,
                    'email' => $club->clubUser->email,
                ] : null,
                'created_at' => $club->created_at?->toISOString(),
                'updated_at' => $club->updated_at?->toISOString(),
            ],
        ], 'Club retrieved successfully.');
    }

    /**
     * Get all available clubs with join status for the authenticated user.
     *
     * @param Request $request
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableClubs(Request $request, ClubFacade $facade)
    {
        $user = auth()->user();
        
        if (!$user) {
            return $this->failResponse('Unauthenticated.', [], 401);
        }

        // Get all active clubs
        $clubs = \App\Models\Club::where('status', 'active')
            ->with(['creator', 'clubUser'])
            ->get();

        // Get membership service to check join status
        $membershipService = app(\App\Services\Club\MembershipService::class);

        $clubsWithStatus = $clubs->map(function ($club) use ($user, $membershipService) {
            $joinStatus = $membershipService->getClubJoinStatus($club, $user);

            return [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'description' => $club->description,
                'email' => $club->email,
                'phone' => $club->phone,
                'logo' => $club->logo ? \Storage::url($club->logo) : null,
                'status' => $club->status,
                'join_status' => $joinStatus['status'],
                'rejected_at' => $joinStatus['rejected_at'] ? (is_string($joinStatus['rejected_at']) ? $joinStatus['rejected_at'] : $joinStatus['rejected_at']->toIso8601String()) : null,
                'removed_at' => $joinStatus['removed_at'] ? (is_string($joinStatus['removed_at']) ? $joinStatus['removed_at'] : $joinStatus['removed_at']->toIso8601String()) : null,
                'cooldown_remaining_days' => $joinStatus['cooldown_remaining_days'],
                'pending_request_id' => $joinStatus['pending_request_id'],
                'blacklist_reason' => $joinStatus['blacklist_reason'] ?? null,
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

        return $this->successResponse([
            'data' => [
                'clubs' => $clubsWithStatus,
                'total' => $clubsWithStatus->count(),
            ],
        ], 'Available clubs retrieved successfully.');
    }
}

