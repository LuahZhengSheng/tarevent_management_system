<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AuthorizationService
{
    /**
     * Authorize action or abort with 403
     * 
     * @param string $ability The ability to check (e.g., 'viewUsers', 'createUser')
     * @param mixed $arguments The arguments to pass to the policy
     * @param string|null $message Custom error message
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    private function authorizeOrAbort(string $ability, $arguments, ?string $message = null): void
    {
        if (!Gate::allows($ability, $arguments)) {
            abort(403, $message ?? 'You do not have permission to perform this action.');
        }
    }

    // ============================================
    // User Management Permissions
    // ============================================

    /**
     * Check if user can view users list
     */
    public function canViewUsers(): bool
    {
        return Gate::allows('viewUsers', User::class);
    }

    /**
     * Check if user can create a user
     */
    public function canCreateUser(): bool
    {
        return Gate::allows('createUser', User::class);
    }

    /**
     * Check if user can view a specific user's details
     */
    public function canViewUserDetails(User $user): bool
    {
        return Gate::allows('viewUserDetails', $user);
    }

    /**
     * Check if user can update a user
     */
    public function canUpdateUser(User $user): bool
    {
        return Gate::allows('updateUser', $user);
    }

    /**
     * Check if user can delete a user
     */
    public function canDeleteUser(User $user): bool
    {
        return Gate::allows('deleteUser', $user);
    }

    /**
     * Check if user can toggle user status
     */
    public function canToggleUserStatus(User $user): bool
    {
        return Gate::allows('toggleUserStatus', $user);
    }

    /**
     * Authorize viewing users list or abort
     */
    public function authorizeViewUsersOrAbort(?string $message = null): void
    {
        $this->authorizeOrAbort('viewUsers', User::class, $message ?? 'You do not have permission to view users.');
    }

    /**
     * Authorize creating user or abort
     */
    public function authorizeCreateUserOrAbort(?string $message = null): void
    {
        $this->authorizeOrAbort('createUser', User::class, $message ?? 'You do not have permission to create users.');
    }

    /**
     * Authorize viewing user details or abort
     */
    public function authorizeViewUserDetailsOrAbort(User $user, ?string $message = null): void
    {
        $this->authorizeOrAbort('viewUserDetails', $user, $message ?? 'You do not have permission to view this user.');
    }

    /**
     * Authorize updating user or abort
     */
    public function authorizeUpdateUserOrAbort(User $user, ?string $message = null): void
    {
        $this->authorizeOrAbort('updateUser', $user, $message ?? 'You do not have permission to update this user.');
    }

    /**
     * Authorize deleting user or abort
     */
    public function authorizeDeleteUserOrAbort(User $user, ?string $message = null): void
    {
        $this->authorizeOrAbort('deleteUser', $user, $message ?? 'You do not have permission to delete this user.');
    }

    /**
     * Authorize toggling user status or abort
     */
    public function authorizeToggleUserStatusOrAbort(User $user, ?string $message = null): void
    {
        $this->authorizeOrAbort('toggleUserStatus', $user, $message ?? 'You do not have permission to toggle this user\'s status.');
    }

    /**
     * Authorize toggling status for any user (automatically determines user type)
     * 
     * @param User $user The user whose status will be toggled
     * @param string|null $message Custom error message
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function authorizeToggleStatusOrAbort(User $user, ?string $message = null): void
    {
        if ($user->isAdministrator()) {
            $this->authorizeToggleAdministratorStatusOrAbort($user, $message);
        } else {
            $this->authorizeToggleUserStatusOrAbort($user, $message);
        }
    }

    // ============================================
    // Administrator Management Permissions
    // ============================================

    /**
     * Check if user can view administrators list
     */
    public function canViewAdministrators(): bool
    {
        return Gate::allows('viewAdministrators', User::class);
    }

    /**
     * Check if user can create an administrator
     */
    public function canCreateAdministrator(): bool
    {
        return Gate::allows('createAdministrator', User::class);
    }

    /**
     * Check if user can view a specific administrator's details
     */
    public function canViewAdministratorDetails(User $admin): bool
    {
        return Gate::allows('viewAdministratorDetails', $admin);
    }

    /**
     * Check if user can update an administrator
     */
    public function canUpdateAdministrator(User $admin): bool
    {
        return Gate::allows('updateAdministrator', $admin);
    }

    /**
     * Check if user can delete an administrator
     */
    public function canDeleteAdministrator(User $admin): bool
    {
        return Gate::allows('deleteAdministrator', $admin);
    }

    /**
     * Check if user can toggle administrator status
     */
    public function canToggleAdministratorStatus(User $admin): bool
    {
        return Gate::allows('toggleAdministratorStatus', $admin);
    }

    /**
     * Check if user can manage permissions
     */
    public function canManagePermissions(): bool
    {
        return Gate::allows('managePermissions', User::class);
    }

    /**
     * Authorize viewing administrators list or abort
     */
    public function authorizeViewAdministratorsOrAbort(?string $message = null): void
    {
        $this->authorizeOrAbort('viewAdministrators', User::class, $message ?? 'You do not have permission to view administrators.');
    }

    /**
     * Authorize creating administrator or abort
     */
    public function authorizeCreateAdministratorOrAbort(?string $message = null): void
    {
        $this->authorizeOrAbort('createAdministrator', User::class, $message ?? 'You do not have permission to create administrators.');
    }

    /**
     * Authorize viewing administrator details or abort
     */
    public function authorizeViewAdministratorDetailsOrAbort(User $admin, ?string $message = null): void
    {
        $this->authorizeOrAbort('viewAdministratorDetails', $admin, $message ?? 'You do not have permission to view this administrator.');
    }

    /**
     * Authorize updating administrator or abort
     */
    public function authorizeUpdateAdministratorOrAbort(User $admin, ?string $message = null): void
    {
        $this->authorizeOrAbort('updateAdministrator', $admin, $message ?? 'You do not have permission to update this administrator.');
    }

    /**
     * Authorize deleting administrator or abort
     */
    public function authorizeDeleteAdministratorOrAbort(User $admin, ?string $message = null): void
    {
        $this->authorizeOrAbort('deleteAdministrator', $admin, $message ?? 'You do not have permission to delete this administrator.');
    }

    /**
     * Authorize toggling administrator status or abort
     */
    public function authorizeToggleAdministratorStatusOrAbort(User $admin, ?string $message = null): void
    {
        $this->authorizeOrAbort('toggleAdministratorStatus', $admin, $message ?? 'You do not have permission to toggle this administrator\'s status.');
    }

    /**
     * Authorize managing permissions or abort
     */
    public function authorizeManagePermissionsOrAbort(?string $message = null): void
    {
        $this->authorizeOrAbort('managePermissions', User::class, $message ?? 'You do not have permission to manage permissions.');
    }

    // ============================================
    // Legacy Methods (for backward compatibility)
    // ============================================

    /**
     * @deprecated Use authorizeViewUsersOrAbort() instead
     */
    public function authorizeManageStudentsOrAbort(?string $message = null): void
    {
        $this->authorizeViewUsersOrAbort($message);
    }

    /**
     * @deprecated Use authorizeViewAdministratorsOrAbort() instead
     */
    public function authorizeManageAdministratorsOrAbort(?string $message = null): void
    {
        $this->authorizeViewAdministratorsOrAbort($message);
    }

    /**
     * @deprecated Use authorizeViewUserDetailsOrAbort() or authorizeViewAdministratorDetailsOrAbort() instead
     */
    public function authorizeViewOrAbort(User $user, ?string $message = null): void
    {
        if (in_array($user->role, ['student', 'club', 'user'])) {
            $this->authorizeViewUserDetailsOrAbort($user, $message);
        } elseif ($user->role === 'admin') {
            $this->authorizeViewAdministratorDetailsOrAbort($user, $message);
        } else {
            abort(403, $message ?? 'You do not have permission to view this user.');
        }
    }

    /**
     * @deprecated Use authorizeUpdateUserOrAbort() or authorizeUpdateAdministratorOrAbort() instead
     */
    public function authorizeUpdateOrAbort(User $user, ?string $message = null): void
    {
        if (in_array($user->role, ['student', 'club', 'user'])) {
            $this->authorizeUpdateUserOrAbort($user, $message);
        } elseif ($user->role === 'admin') {
            $this->authorizeUpdateAdministratorOrAbort($user, $message);
        } else {
            abort(403, $message ?? 'You do not have permission to update this user.');
        }
    }
}

