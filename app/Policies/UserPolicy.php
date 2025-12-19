<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Policies;

use App\Models\User;
use App\Constants\PermissionConstants;

class UserPolicy
{
    /**
     * Super Admin always has all permissions
     */
    private function checkSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    // ============================================
    // User Management Permissions
    // ============================================

    /**
     * Determine if the user can view users list.
     */
    public function viewUsers(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::USER_VIEW);
    }

    /**
     * Determine if the user can create a user.
     */
    public function createUser(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::USER_CREATE);
    }

    /**
     * Determine if the user can view a specific user's details.
     */
    public function viewUserDetails(User $user, User $model): bool
    {
        // Can view own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super admin can view all
        if ($this->checkSuperAdmin($user)) {
            return true;
        }

        // Check permission for viewing user details
        if (!$user->hasPermission(PermissionConstants::USER_VIEW_DETAILS)) {
            return false;
        }

        // Admin with permission can view students/clubs
        if (in_array($model->role, ['student', 'club', 'user'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update a user.
     */
    public function updateUser(User $user, User $model): bool
    {
        // Can update own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super admin can update all
        if ($this->checkSuperAdmin($user)) {
            return true;
        }

        // Check permission for updating user
        if (!$user->hasPermission(PermissionConstants::USER_UPDATE)) {
            return false;
        }

        // Admin with permission can update students/clubs
        if (in_array($model->role, ['student', 'club', 'user'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete a user.
     */
    public function deleteUser(User $user, User $model): bool
    {
        // Cannot delete own account
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can delete all (except other super admins)
        if ($this->checkSuperAdmin($user)) {
            return !$model->isSuperAdmin();
        }

        // Check permission for deleting user
        if (!$user->hasPermission(PermissionConstants::USER_DELETE)) {
            return false;
        }

        // Admin with permission can delete students/clubs
        return in_array($model->role, ['student', 'club', 'user']);
    }

    /**
     * Determine if the user can toggle user status.
     */
    public function toggleUserStatus(User $user, User $model): bool
    {
        // Cannot toggle own status
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can toggle all
        if ($this->checkSuperAdmin($user)) {
            return true;
        }

        // Check permission for toggling user status
        if (!$user->hasPermission(PermissionConstants::USER_TOGGLE_STATUS)) {
            return false;
        }

        // Admin with permission can toggle students/clubs
        return in_array($model->role, ['student', 'club', 'user']);
    }

    // ============================================
    // Administrator Management Permissions
    // ============================================

    /**
     * Determine if the user can view administrators list.
     */
    public function viewAdministrators(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::ADMIN_VIEW);
    }

    /**
     * Determine if the user can create an administrator.
     */
    public function createAdministrator(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::ADMIN_CREATE);
    }

    /**
     * Determine if the user can view a specific administrator's details.
     */
    public function viewAdministratorDetails(User $user, User $model): bool
    {
        // Can view own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super admin can view all
        if ($this->checkSuperAdmin($user)) {
            return true;
        }

        // Check permission for viewing administrator details
        if (!$user->hasPermission(PermissionConstants::ADMIN_VIEW_DETAILS)) {
            return false;
        }

        // Admin with permission can view other admins (but not super admins)
        return $model->role === 'admin' && !$model->isSuperAdmin();
    }

    /**
     * Determine if the user can update an administrator.
     */
    public function updateAdministrator(User $user, User $model): bool
    {
        // Can update own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super admin can update all (except other super admins)
        if ($this->checkSuperAdmin($user)) {
            return !$model->isSuperAdmin();
        }

        // Check permission for updating administrator
        if (!$user->hasPermission(PermissionConstants::ADMIN_UPDATE)) {
            return false;
        }

        // Admin with permission can update other admins (but not super admins)
        return $model->role === 'admin' && !$model->isSuperAdmin();
    }

    /**
     * Determine if the user can delete an administrator.
     */
    public function deleteAdministrator(User $user, User $model): bool
    {
        // Cannot delete own account
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can delete admins (but not other super admins)
        if ($this->checkSuperAdmin($user)) {
            return !$model->isSuperAdmin();
        }

        // Check permission for deleting administrator
        if (!$user->hasPermission(PermissionConstants::ADMIN_DELETE)) {
            return false;
        }

        // Admin with permission can delete other admins (but not super admins)
        return $model->role === 'admin' && !$model->isSuperAdmin();
    }

    /**
     * Determine if the user can toggle administrator status.
     */
    public function toggleAdministratorStatus(User $user, User $model): bool
    {
        // Cannot toggle own status
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can toggle all
        if ($this->checkSuperAdmin($user)) {
            return true;
        }

        // Check permission for toggling administrator status
        if (!$user->hasPermission(PermissionConstants::ADMIN_TOGGLE_STATUS)) {
            return false;
        }

        // Admin with permission can toggle other admins (but not super admins)
        return $model->role === 'admin' && !$model->isSuperAdmin();
    }

    /**
     * Determine if the user can manage permissions.
     */
    public function managePermissions(User $user): bool
    {
        // Only super admin can manage permissions
        return $this->checkSuperAdmin($user);
    }

    // ============================================
    // Other Module Permissions (Simplified)
    // ============================================

    /**
     * Determine if the user can manage events.
     */
    public function manageEvents(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::EVENT_MANAGE);
    }

    /**
     * Determine if the user can manage clubs.
     */
    public function manageClubs(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::CLUB_MANAGE);
    }

    /**
     * Determine if the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::VIEW_REPORTS);
    }

    /**
     * Determine if the user can manage system settings.
     */
    public function manageSettings(User $user): bool
    {
        return $this->checkSuperAdmin($user) || $user->hasPermission(PermissionConstants::MANAGE_SETTINGS);
    }

    // ============================================
    // Legacy Methods (for backward compatibility)
    // ============================================

    /**
     * Determine if the user can view any user profile.
     * @deprecated Use viewUsers() instead
     */
    public function viewAny(User $user): bool
    {
        return $this->viewUsers($user);
    }

    /**
     * Determine if the user can view the user profile.
     * @deprecated Use viewUserDetails() or viewAdministratorDetails() instead
     */
    public function view(User $user, User $model): bool
    {
        if (in_array($model->role, ['student', 'club', 'user'])) {
            return $this->viewUserDetails($user, $model);
        } elseif ($model->role === 'admin') {
            return $this->viewAdministratorDetails($user, $model);
        }
        return false;
    }

    /**
     * Determine if the user can update the user profile.
     * @deprecated Use updateUser() or updateAdministrator() instead
     */
    public function update(User $user, User $model): bool
    {
        if (in_array($model->role, ['student', 'club', 'user'])) {
            return $this->updateUser($user, $model);
        } elseif ($model->role === 'admin') {
            return $this->updateAdministrator($user, $model);
        }
        return false;
    }
}
