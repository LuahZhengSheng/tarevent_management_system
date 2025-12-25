<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubAnnouncement;
use App\Models\User;
use App\Services\ClubFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClubApiController extends Controller
{
    /**
     * Get error code based on HTTP status code
     * 
     * @param string $status Status code: S (Success), F (Fail), E (Error)
     * @param int $httpStatusCode HTTP status code
     * @return string Error code (HTTP status code as string)
     */
    protected function getErrorCode(string $status, int $httpStatusCode): string
    {
        // Success: no error code
        if ($status === 'S') {
            return '';
        }

        // Return HTTP status code as error code
        return (string) $httpStatusCode;
    }

    /**
     * Format API response according to IFA standards
     * 
     * @param string $status Status code: S (Success), F (Fail), E (Error)
     * @param array $data Response data
     * @param int $httpStatusCode HTTP status code
     * @param string|null $message Optional message
     * @param string|null $errorCode Optional error code (auto-generated if not provided)
     * @return JsonResponse
     */
    protected function formatResponse(
        string $status,
        array $data = [],
        int $httpStatusCode = 200,
        ?string $message = null,
        ?string $errorCode = null
    ): JsonResponse {
        // IFA standard fields
        $response = [
            'status' => $status,  // IFA standard: S/F/E
            'timestamp' => now()->format('Y-m-d H:i:s'),  // IFA standard: YYYY-MM-DD HH:MM:SS
        ];

        // Add error code for Fail and Error status
        if ($status !== 'S') {
            $response['error_code'] = $errorCode ?? $this->getErrorCode($status, $httpStatusCode);
        }

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
     * @param string|null $errorCode Optional error code (auto-generated if not provided)
     * @return JsonResponse
     */
    protected function failResponse(
        string $message,
        array $data = [],
        int $httpStatusCode = 400,
        ?string $errorCode = null
    ): JsonResponse {
        return $this->formatResponse('F', $data, $httpStatusCode, $message, $errorCode);
    }

    /**
     * Format error response (Status: E)
     * 
     * @param string $message Error message
     * @param array $data Additional data
     * @param int $httpStatusCode HTTP status code (default: 500)
     * @param string|null $errorCode Optional error code (auto-generated if not provided)
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message,
        array $data = [],
        int $httpStatusCode = 500,
        ?string $errorCode = null
    ): JsonResponse {
        return $this->formatResponse('E', $data, $httpStatusCode, $message, $errorCode);
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
            'category' => ['nullable', 'string', 'max:100', 'in:academic,sports,cultural,social,volunteer,professional,other'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:clubs,email'],
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
            'category' => ['nullable', 'string', 'max:100', 'in:academic,sports,cultural,social,volunteer,professional,other'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:clubs,email,' . $club->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Handle background image file upload if present
        if ($request->hasFile('background_image')) {
            // Delete old background image if exists
            if ($club->background_image) {
                Storage::disk('public')->delete($club->background_image);
            }
            
            $backgroundImageFile = $request->file('background_image');
            $backgroundImagePath = $backgroundImageFile->store('clubs/backgrounds', 'public');
            $validated['background_image'] = $backgroundImagePath;
        }

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
            // Validate IFA standard: timestamp or requestID must be provided
            $request->validate([
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            // Ensure at least one of timestamp or requestID is provided
            if (!$request->filled('timestamp') && !$request->filled('requestID')) {
                return $this->failResponse('Either timestamp or requestID must be provided.', [], 400);
            }

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
     * Get join requests for a club.
     *
     * @param Request $request
     * @param Club $club
     * @return JsonResponse
     */
    public function getJoinRequests(Request $request, Club $club): JsonResponse
    {
        try {
            // Validate IFA standard: timestamp or requestID must be provided
            $request->validate([
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
                'status' => ['nullable', 'string', 'in:pending,approved,rejected'],
            ]);

            // Ensure at least one of timestamp or requestID is provided
            if (!$request->filled('timestamp') && !$request->filled('requestID')) {
                return $this->failResponse('Either timestamp or requestID must be provided.', [], 400);
            }

            $status = $request->input('status');

            $query = $club->joinRequests()->with('user');

            if ($status) {
                $query->where('status', $status);
            }

            $joinRequests = $query->orderBy('created_at', 'desc')->get();

            $requestsData = $joinRequests->map(function ($joinRequest) {
                return [
                    'id' => $joinRequest->id,
                    'user' => [
                        'id' => $joinRequest->user->id,
                        'name' => $joinRequest->user->name,
                        'email' => $joinRequest->user->email,
                        'profile_photo_url' => $joinRequest->user->profile_photo_url,
                    ],
                    'status' => $joinRequest->status,
                    'reason' => $joinRequest->description,
                    'rejection_reason' => null, // Not stored in database, handled via metadata if needed
                    'created_at' => $joinRequest->created_at->toISOString(),
                    'updated_at' => $joinRequest->updated_at->toISOString(),
                    'approved_at' => $joinRequest->status === 'approved' ? $joinRequest->updated_at->toISOString() : null,
                    'rejected_at' => $joinRequest->status === 'rejected' ? $joinRequest->updated_at->toISOString() : null,
                ];
            });

            return $this->successResponse([
                'data' => [
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'status_filter' => $status,
                    'total_requests' => $joinRequests->count(),
                    'requests' => $requestsData,
                ],
            ], 'Join requests retrieved successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Approve a join request.
     *
     * @param Request $request
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function approveJoin(Request $request, Club $club, User $user, ClubFacade $facade): JsonResponse
    {
        try {
            // Validate IFA standard
            $request->validate([
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            $facade->approveJoin($club, $user, auth()->user());

            return $this->successResponse([
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ],
            ], 'Join request approved successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reject a join request.
     *
     * @param Request $request
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function rejectJoin(Request $request, Club $club, User $user, ClubFacade $facade): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => ['nullable', 'string', 'max:500'],
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            $facade->rejectJoin($club, $user, auth()->user(), $validated['reason'] ?? null);

            return $this->successResponse([
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'rejection_reason' => $validated['reason'] ?? null,
                ],
            ], 'Join request rejected successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all clubs for a specific user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserClubs(Request $request, User $user)
    {
        // Validate IFA standard: timestamp or requestID must be provided
        $request->validate([
            'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
            'requestID' => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure at least one of timestamp or requestID is provided
        if (!$request->filled('timestamp') && !$request->filled('requestID')) {
            return $this->failResponse('Either timestamp or requestID must be provided.', [], 400);
        }

        // Extract requestID for logging (prefer requestID over timestamp)
        $requestId = $request->input('requestID') ?? $request->input('timestamp');

        $clubs = $user->clubs()
            ->with(['creator', 'clubUser'])
            ->get()
            ->map(function ($club) use ($user) {
                return [
                    'id' => $club->id,
                    'name' => $club->name,
                    'slug' => $club->slug,
                    'description' => $club->description,
                    'category' => $club->category,
                    'email' => $club->email,
                    'phone' => $club->phone,
                    'logo' => $club->logo ? '/storage/' . $club->logo : null,
                    'background_image' => $club->background_image ? '/storage/' . $club->background_image : null,
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
     * @param Request $request
     * @param Club $club
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Club $club, ClubFacade $facade)
    {
        // Validate IFA standard: timestamp or requestID must be provided
        $request->validate([
            'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
            'requestID' => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure at least one of timestamp or requestID is provided
        if (!$request->filled('timestamp') && !$request->filled('requestID')) {
            return $this->failResponse('Either timestamp or requestID must be provided.', [], 400);
        }

        // Extract requestID for logging (prefer requestID over timestamp)
        $requestId = $request->input('requestID') ?? $request->input('timestamp');

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

        // Log the view action (optional - for tracking API usage)
        // Uncomment if you want to log GET requests
        // $facade->logClubView($club, $user, $requestId);

        return $this->successResponse([
            'data' => [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'description' => $club->description,
                'category' => $club->category,
                'email' => $club->email,
                'phone' => $club->phone,
                'logo' => $club->logo ? '/storage/' . $club->logo : null,
                'background_image' => $club->background_image ? '/storage/' . $club->background_image : null,
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
        // Validate IFA standard: timestamp or requestID must be provided
        $request->validate([
            'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
            'requestID' => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure at least one of timestamp or requestID is provided
        if (!$request->filled('timestamp') && !$request->filled('requestID')) {
            return $this->failResponse('Either timestamp or requestID must be provided.', [], 400);
        }

        // Extract requestID for logging (prefer requestID over timestamp)
        $requestId = $request->input('requestID') ?? $request->input('timestamp');

        $user = auth()->user();
        
        if (!$user) {
            return $this->failResponse('Unauthenticated.', [], 401);
        }

        // Get all active clubs with optional category filter
        $query = \App\Models\Club::where('status', 'active')
            ->with(['creator', 'clubUser']);
        
        // Apply category filter if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        $clubs = $query->get();

        // Get membership service to check join status
        $membershipService = app(\App\Services\Club\MembershipService::class);

        $clubsWithStatus = $clubs->map(function ($club) use ($user, $membershipService) {
            $joinStatus = $membershipService->getClubJoinStatus($club, $user);

            return [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'description' => $club->description,
                'category' => $club->category,
                'email' => $club->email,
                'phone' => $club->phone,
                'logo' => $club->logo ? '/storage/' . $club->logo : null,
                'background_image' => $club->background_image ? '/storage/' . $club->background_image : null,
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

    // ============================================
    // Announcement Management Methods
    // ============================================

    /**
     * Get all announcements for a club.
     *
     * @param Club $club
     * @param Request $request
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function getAnnouncements(Club $club, Request $request, ClubFacade $facade): JsonResponse
    {
        $filters = [];
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $filters['status'] = $request->input('status');
        }
        
        // Limit results if provided
        if ($request->filled('limit')) {
            $filters['limit'] = (int) $request->input('limit');
        }

        $announcements = $facade->getAnnouncements($club, $filters);

        return $this->successResponse([
            'data' => [
                'club_id' => $club->id,
                'club_name' => $club->name,
                'announcements' => $announcements->map(function ($announcement) {
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'content' => $announcement->content,
                        'image' => $announcement->image ? '/storage/' . $announcement->image : null,
                        'status' => $announcement->status,
                        'published_at' => $announcement->published_at?->toISOString(),
                        'created_at' => $announcement->created_at->toISOString(),
                        'updated_at' => $announcement->updated_at->toISOString(),
                        'creator' => $announcement->creator ? [
                            'id' => $announcement->creator->id,
                            'name' => $announcement->creator->name,
                        ] : null,
                    ];
                }),
                'total' => $announcements->count(),
            ],
        ], 'Announcements retrieved successfully.');
    }

    /**
     * Get a single announcement.
     *
     * @param Club $club
     * @param int $announcementId
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function getAnnouncement(Club $club, int $announcementId, ClubFacade $facade): JsonResponse
    {
        $announcement = $facade->getAnnouncement($club, $announcementId);

        if (!$announcement) {
            return $this->failResponse('Announcement not found.', [], 404);
        }

        return $this->successResponse([
            'data' => [
                'id' => $announcement->id,
                'club_id' => $announcement->club_id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'image' => $announcement->image ? '/storage/' . $announcement->image : null,
                'status' => $announcement->status,
                'published_at' => $announcement->published_at?->toISOString(),
                'created_at' => $announcement->created_at->toISOString(),
                'updated_at' => $announcement->updated_at->toISOString(),
                'creator' => $announcement->creator ? [
                    'id' => $announcement->creator->id,
                    'name' => $announcement->creator->name,
                ] : null,
            ],
        ], 'Announcement retrieved successfully.');
    }

    /**
     * Create a new announcement.
     *
     * @param Club $club
     * @param Request $request
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function createAnnouncement(Club $club, Request $request, ClubFacade $facade): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'status' => ['nullable', 'string', 'in:draft,published'],
            ]);

            // Handle image file upload if present
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imagePath = $imageFile->store('clubs/announcements', 'public');
                $validated['image'] = $imagePath;
            }

            $announcement = $facade->createAnnouncement(
                $club,
                $validated,
                auth()->user()
            );

            return $this->successResponse([
                'data' => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'image' => $announcement->image ? '/storage/' . $announcement->image : null,
                    'status' => $announcement->status,
                    'published_at' => $announcement->published_at?->toISOString(),
                    'created_at' => $announcement->created_at->toISOString(),
                ],
            ], 'Announcement created successfully.', 201);
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update an announcement.
     *
     * @param Club $club
     * @param ClubAnnouncement $announcement
     * @param Request $request
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function updateAnnouncement(Club $club, ClubAnnouncement $announcement, Request $request, ClubFacade $facade): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => ['sometimes', 'string', 'max:255'],
                'content' => ['sometimes', 'string'],
                'status' => ['sometimes', 'string', 'in:draft,published'],
            ]);

            $announcement = $facade->updateAnnouncement(
                $club,
                $announcement,
                $validated,
                auth()->user()
            );

            return $this->successResponse([
                'data' => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'image' => $announcement->image ? '/storage/' . $announcement->image : null,
                    'status' => $announcement->status,
                    'published_at' => $announcement->published_at?->toISOString(),
                    'updated_at' => $announcement->updated_at->toISOString(),
                ],
            ], 'Announcement updated successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete an announcement.
     *
     * @param Club $club
     * @param ClubAnnouncement $announcement
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function deleteAnnouncement(Club $club, ClubAnnouncement $announcement, ClubFacade $facade): JsonResponse
    {
        try {
            $facade->deleteAnnouncement($club, $announcement, auth()->user());

            return $this->successResponse([], 'Announcement deleted successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Publish an announcement.
     *
     * @param Club $club
     * @param ClubAnnouncement $announcement
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function publishAnnouncement(Club $club, ClubAnnouncement $announcement, ClubFacade $facade): JsonResponse
    {
        try {
            $announcement = $facade->publishAnnouncement($club, $announcement, auth()->user());

            return $this->successResponse([
                'data' => [
                    'id' => $announcement->id,
                    'status' => $announcement->status,
                    'published_at' => $announcement->published_at->toISOString(),
                ],
            ], 'Announcement published successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Unpublish an announcement.
     *
     * @param Club $club
     * @param ClubAnnouncement $announcement
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function unpublishAnnouncement(Club $club, ClubAnnouncement $announcement, ClubFacade $facade): JsonResponse
    {
        try {
            $announcement = $facade->unpublishAnnouncement($club, $announcement, auth()->user());

            return $this->successResponse([
                'data' => [
                    'id' => $announcement->id,
                    'status' => $announcement->status,
                    'published_at' => $announcement->published_at,
                ],
            ], 'Announcement unpublished successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ============================================
    // Member Management APIs (IFA Compliant)
    // ============================================

    /**
     * Get club members list.
     *
     * @param Request $request
     * @param Club $club
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function getMembers(Request $request, Club $club, ClubFacade $facade): JsonResponse
    {
        try {
            // Validate IFA standard: timestamp or requestID must be provided
            $request->validate([
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
                'status' => ['nullable', 'string', 'in:active,removed'],
            ]);

            // Ensure at least one of timestamp or requestID is provided
            if (!$request->filled('timestamp') && !$request->filled('requestID')) {
                return $this->failResponse('Either timestamp or requestID must be provided.', [], 400);
            }

            $status = $request->input('status', 'active');

            // Get members based on status
            if ($status === 'active') {
                $members = $club->members()
                    ->wherePivot('status', 'active')
                    ->withPivot('role', 'status', 'created_at')
                    ->orderBy('club_user.created_at', 'desc')
                    ->get();
            } else {
                $members = $club->members()
                    ->wherePivot('status', 'removed')
                    ->withPivot('role', 'status', 'created_at')
                    ->orderBy('club_user.created_at', 'desc')
                    ->get();
            }

            $membersData = $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'profile_photo_url' => $member->profile_photo_url,
                    'role' => $member->pivot->role,
                    'role_display_name' => \App\Models\ClubMemberRole::displayName($member->pivot->role),
                    'joined_at' => $member->pivot->created_at ? $member->pivot->created_at->toISOString() : null,
                ];
            });

            return $this->successResponse([
                'data' => [
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'status' => $status,
                    'total_members' => $members->count(),
                    'members' => $membersData,
                ],
            ], 'Members retrieved successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get blacklisted users for a club.
     *
     * @param Request $request
     * @param Club $club
     * @return JsonResponse
     */
    public function getBlacklist(Request $request, Club $club): JsonResponse
    {
        try {
            // Validate IFA standard: timestamp or requestID must be provided
            $request->validate([
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            // Ensure at least one of timestamp or requestID is provided
            if (!$request->filled('timestamp') && !$request->filled('requestID')) {
                return $this->failResponse('Either timestamp or requestID must be provided.', [], 400);
            }

            $blacklistedUsers = $club->blacklistedUsers()
                ->withPivot('reason', 'blacklisted_by', 'created_at')
                ->orderBy('club_blacklist.created_at', 'desc')
                ->get();

            $blacklistData = $blacklistedUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $user->profile_photo_url,
                    'reason' => $user->pivot->reason,
                    'blacklisted_at' => $user->pivot->created_at ? $user->pivot->created_at->toISOString() : null,
                ];
            });

            return $this->successResponse([
                'data' => [
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'total_blacklisted' => $blacklistedUsers->count(),
                    'blacklisted_users' => $blacklistData,
                ],
            ], 'Blacklist retrieved successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update member role.
     *
     * @param Request $request
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function updateMemberRole(Request $request, Club $club, User $user, ClubFacade $facade): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role' => ['required', 'string', 'in:' . implode(',', \App\Models\ClubMemberRole::all())],
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            $facade->updateMemberRole($club, $user, $validated['role'], auth()->user());

            return $this->successResponse([
                'data' => [
                    'member_id' => $user->id,
                    'member_name' => $user->name,
                    'new_role' => $validated['role'],
                    'role_display_name' => \App\Models\ClubMemberRole::displayName($validated['role']),
                ],
            ], 'Member role updated successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove member from club.
     *
     * @param Request $request
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function removeMember(Request $request, Club $club, User $user, ClubFacade $facade): JsonResponse
    {
        try {
            // Validate IFA standard
            $request->validate([
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            $facade->removeMember($club, $user, auth()->user());

            return $this->successResponse([
                'data' => [
                    'member_id' => $user->id,
                    'member_name' => $user->name,
                ],
            ], 'Member removed successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Add user to blacklist.
     *
     * @param Request $request
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function addToBlacklist(Request $request, Club $club, User $user, ClubFacade $facade): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => ['nullable', 'string', 'max:500'],
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            $facade->addToBlacklist($club, $user, $validated['reason'] ?? null, auth()->user());

            return $this->successResponse([
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'reason' => $validated['reason'] ?? null,
                ],
            ], 'User added to blacklist successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove user from blacklist.
     *
     * @param Request $request
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return JsonResponse
     */
    public function removeFromBlacklist(Request $request, Club $club, User $user, ClubFacade $facade): JsonResponse
    {
        try {
            // Validate IFA standard
            $request->validate([
                'timestamp' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
                'requestID' => ['nullable', 'string', 'max:255'],
            ]);

            $facade->removeFromBlacklist($club, $user, auth()->user());

            return $this->successResponse([
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                ],
            ], 'User removed from blacklist successfully.');
        } catch (\Exception $e) {
            return $this->failResponse($e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

