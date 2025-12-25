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
     * Display a listing of club users
     * GET /api/v1/club-users
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::where('role', 'club')
            ->latest()
            ->paginate($perPage);

        return $this->successResponse([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ], 'Club users retrieved successfully.');
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
            
            // Get validated data (excluding timestamp/requestID as they're only for tracking)
            $validated = $request->validated();
            unset($validated['timestamp'], $validated['requestID']); // Remove tracking fields from user creation data
            
            // Reuse existing UserService to create user
            $user = $this->userService->createUser($validated, $strategy);

            return $this->successResponse([
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
            ], 'Club user created successfully.', 201);

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create club user.',
                ['error' => config('app.debug') ? $e->getMessage() : 'Internal server error.'],
                500
            );
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
            return $this->failResponse('User is not a club user.', [], 404);
        }

        return $this->successResponse([
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
        ], 'Club user retrieved successfully.');
    }
}

