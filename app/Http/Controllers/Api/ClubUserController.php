<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateClubUserRequest;
use App\Models\User;
use App\Services\UserService;
use App\Services\Strategies\AdminCreatedStudentStrategy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClubUserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Format API response with status and timestamp
     */
    private function formatResponse(array $data, int $statusCode = 200): JsonResponse
    {
        $response = array_merge([
            'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
            'timestamp' => now()->timestamp,
        ], $data);

        return response()->json($response, $statusCode);
    }

    /**
     * Display a listing of club users
     * GET /api/v1/club-users
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::where('role', 'club')
            ->latest()
            ->paginate($perPage);

        return $this->formatResponse([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created club user
     * POST /api/v1/club-users
     */
    public function store(CreateClubUserRequest $request): JsonResponse
    {
        try {
            // Reuse existing AdminCreatedStudentStrategy with 'club' role
            $strategy = new AdminCreatedStudentStrategy('club');
            
            // Get validated data (excluding timestamp as it's only for tracking)
            $validated = $request->validated();
            unset($validated['timestamp']); // Remove timestamp from user creation data
            
            // Reuse existing UserService to create user
            $user = $this->userService->createUser($validated, $strategy);

            return $this->formatResponse([
                'success' => true,
                'message' => 'Club user created successfully.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id,
                    'phone' => $user->phone,
                    'program' => $user->program,
                    'role' => $user->role,
                    'status' => $user->status,
                    'club_id' => $user->club_id,
                    'created_at' => $user->created_at->toISOString(),
                ],
            ], 201);

        } catch (\Exception $e) {
            return $this->formatResponse([
                'success' => false,
                'message' => 'Failed to create club user.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ], 500);
        }
    }

    /**
     * Display the specified club user
     * GET /api/v1/club-users/{user}
     */
    public function show(User $user): JsonResponse
    {
        // Ensure it's a club user
        if ($user->role !== 'club') {
            return $this->formatResponse([
                'success' => false,
                'message' => 'User is not a club user.',
            ], 404);
        }

        return $this->formatResponse([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_id' => $user->student_id,
                'phone' => $user->phone,
                'program' => $user->program,
                'role' => $user->role,
                'status' => $user->status,
                'club_id' => $user->club_id,
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
            ],
        ]);
    }
}

