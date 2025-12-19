<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PermissionController extends Controller
{
    /**
     * Get available permissions list
     */
    private function getAvailablePermissions(): array
    {
        return [
            'manage_students' => 'Manage Students',
            'manage_administrators' => 'Manage Administrators',
            'manage_events' => 'Manage Events',
            'manage_clubs' => 'Manage Clubs',
            'view_reports' => 'View Reports',
            'manage_settings' => 'Manage System Settings',
        ];
    }

    /**
     * Display a listing of administrators with their permissions
     */
    public function index(): View
    {
        // Only super admin can access
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super administrators can manage permissions.');
        }

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
        // Only super admin can access
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super administrators can manage permissions.');
        }

        // Cannot edit super admin permissions
        if ($admin->isSuperAdmin()) {
            abort(403, 'Cannot edit super administrator permissions.');
        }

        // Can only edit admin role
        if ($admin->role !== 'admin') {
            abort(403, 'Can only edit administrator permissions.');
        }

        $permissions = $this->getAvailablePermissions();

        return view('admin.permissions.edit', compact('admin', 'permissions'));
    }

    /**
     * Update administrator permissions
     */
    public function update(Request $request, User $admin): RedirectResponse
    {
        // Only super admin can access
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super administrators can manage permissions.');
        }

        // Cannot edit super admin permissions
        if ($admin->isSuperAdmin()) {
            abort(403, 'Cannot edit super administrator permissions.');
        }

        // Can only edit admin role
        if ($admin->role !== 'admin') {
            abort(403, 'Can only edit administrator permissions.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys($this->getAvailablePermissions()))],
        ]);

        // Update permissions
        $admin->permissions = $validated['permissions'] ?? null;
        $admin->save();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully for ' . $admin->name . '.');
    }
}
