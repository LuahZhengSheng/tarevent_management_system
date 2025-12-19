<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthorizationService;
use App\Constants\PermissionConstants;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PermissionController extends Controller
{
    public function __construct(
        private AuthorizationService $authorizationService
    ) {}

    /**
     * Get available permissions list grouped by module
     */
    private function getAvailablePermissions(): array
    {
        return PermissionConstants::getAllPermissionsByModule();
    }

    /**
     * Display a listing of administrators with their permissions
     */
    public function index(): View
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeManagePermissionsOrAbort();

        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->orderBy('role', 'desc')
            ->orderBy('name')
            ->get();

        $permissions = $this->getAvailablePermissions();

        return view('admin.permissions.index', compact('admins', 'permissions'));
    }

    /**
     * Show the form for editing administrator permissions
     */
    public function edit(User $admin): View
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeManagePermissionsOrAbort();

        // Cannot edit super admin permissions
        if ($admin->isSuperAdmin()) {
            abort(403, 'Cannot edit super administrator permissions.');
        }

        // Can only edit admin role
        if ($admin->role !== 'admin') {
            abort(403, 'Can only edit administrator permissions.');
        }

        $permissions = $this->getAvailablePermissions();
        $allPermissionKeys = PermissionConstants::getPermissionKeys();

        return view('admin.permissions.edit', compact('admin', 'permissions', 'allPermissionKeys'));
    }

    /**
     * Update administrator permissions
     */
    public function update(Request $request, User $admin): RedirectResponse
    {
        // Check permission using AuthorizationService
        $this->authorizationService->authorizeManagePermissionsOrAbort();

        // Cannot edit super admin permissions
        if ($admin->isSuperAdmin()) {
            abort(403, 'Cannot edit super administrator permissions.');
        }

        // Can only edit admin role
        if ($admin->role !== 'admin') {
            abort(403, 'Can only edit administrator permissions.');
        }

        $allPermissionKeys = PermissionConstants::getPermissionKeys();
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', $allPermissionKeys)],
        ]);

        // Update permissions
        $admin->permissions = $validated['permissions'] ?? null;
        $admin->save();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully for ' . $admin->name . '.');
    }
}
