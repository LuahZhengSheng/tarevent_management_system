<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\CreateAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Services\UserService;
use App\Services\AvatarService;
use App\Services\UserQueryService;
use App\Services\AuthorizationService;
use App\Services\UserStatusService;
use App\Services\Strategies\AdminCreatedAdminStrategy;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function __construct(
        private UserService $userService,
        private AvatarService $avatarService,
        private UserQueryService $queryService,
        private AuthorizationService $authorizationService,
        private UserStatusService $statusService
    ) {}
    /**
     * Display a listing of administrators
     */
    public function index(Request $request): View|JsonResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeViewAdministratorsOrAbort();

        $query = $this->queryService->buildAdminListQuery($request);
        $admins = $this->queryService->paginate($query, $request);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.administrators.partials.admin-table', compact('admins'))->render(),
                'pagination' => view('admin.administrators.partials.pagination', compact('admins'))->render(),
            ]);
        }

        return view('admin.administrators.index', compact('admins'));
    }

    /**
     * Show the form for creating a new administrator
     */
    public function create(): View
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeCreateAdministratorOrAbort();

        return view('admin.administrators.create');
    }

    /**
     * Store a newly created administrator
     */
    public function store(CreateAdminRequest $request): RedirectResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeCreateAdministratorOrAbort('You do not have permission to create administrators.');

        try {
            // Use AdminCreatedAdminStrategy for admin-created administrators
            $strategy = new AdminCreatedAdminStrategy();
            $admin = $this->userService->createUser($request->validated(), $strategy);

            return redirect()->route('admin.administrators.show', $admin)
                ->with('success', 'Administrator created successfully. A welcome email with login credentials has been sent to ' . $admin->email . '.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified administrator
     */
    public function show(User $admin): View
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeViewAdministratorDetailsOrAbort($admin);

        return view('admin.administrators.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified administrator
     */
    public function edit(User $admin): View
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeUpdateAdministratorOrAbort($admin);

        return view('admin.administrators.edit', compact('admin'));
    }

    /**
     * Update the specified administrator
     */
    public function update(UpdateAdminRequest $request, User $admin): RedirectResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeUpdateAdministratorOrAbort($admin);

        try {
            // Update admin data
            $this->userService->updateUser($admin, $request->validated());

            // Handle avatar upload (optional)
            if ($request->hasFile('avatar')) {
                $this->avatarService->uploadAvatar($admin, $request->file('avatar'));
            }

            return redirect()->route('admin.administrators.show', $admin)
                ->with('success', 'Administrator updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Toggle administrator status (activate/deactivate)
     */
    public function toggleStatus(Request $request, User $admin): JsonResponse
    {
        // Use unified authorization method (automatically determines user type)
        $this->authorizationService->authorizeToggleStatusOrAbort($admin);

        // Use UserStatusService to toggle status
        $newStatus = $this->statusService->toggleStatus($admin);

        return response()->json([
            'success' => true,
            'message' => 'Administrator status updated successfully.',
            'status' => $newStatus,
        ]);
    }
}

