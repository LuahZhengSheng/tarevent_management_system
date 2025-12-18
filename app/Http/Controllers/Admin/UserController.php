<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\UserService;
use App\Services\AvatarService;
use App\Services\UserQueryService;
use App\Services\ProgramOptionsProvider;
use App\Services\AuthorizationService;
use App\Services\UserStatusService;
use App\Services\Strategies\AdminCreatedStudentStrategy;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private AvatarService $avatarService,
        private UserQueryService $queryService,
        private ProgramOptionsProvider $programProvider,
        private AuthorizationService $authorizationService,
        private UserStatusService $statusService
    ) {}

    /**
     * Show the form for creating a new user
     */
    public function create(): View
    {
        $programOptions = $this->programProvider->getOptions();
        return view('admin.users.create', compact('programOptions'));
    }

    /**
     * Store a newly created user
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeManageStudentsOrAbort('You do not have permission to create users.');

        try {
            // Use AdminCreatedStudentStrategy for admin-created students/clubs
            $role = $request->validated()['role'];
            $strategy = new AdminCreatedStudentStrategy($role);
            $user = $this->userService->createUser($request->validated(), $strategy);

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'User created successfully. A welcome email with login credentials has been sent to ' . $user->email . '.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display a listing of users (students and clubs)
     */
    public function index(Request $request): View|JsonResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeManageStudentsOrAbort();

        $query = $this->queryService->buildUserListQuery($request);
        $users = $this->queryService->paginate($query, $request);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.user-table', compact('users'))->render(),
                'pagination' => view('admin.users.partials.pagination', compact('users'))->render(),
            ]);
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user
     */
    public function show(User $user): View
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeViewOrAbort($user);

        $user->load(['club', 'eventRegistrations', 'posts']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user): View
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeUpdateOrAbort($user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeUpdateOrAbort($user);

        try {
            // Update user data
            $this->userService->updateUser($user, $request->validated());

            // Handle avatar upload (optional)
            if ($request->hasFile('avatar')) {
                $this->avatarService->uploadAvatar($user, $request->file('avatar'));
            }

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'User updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeUpdateOrAbort($user);

        // Use UserStatusService to toggle status
        $newStatus = $this->statusService->toggleStatus($user);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'status' => $newStatus,
        ]);
    }
}

