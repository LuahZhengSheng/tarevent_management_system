<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can manage students.
     */
    public function manageStudents(User $user): bool
    {
        return $user->hasPermission('manage_students');
    }

    /**
     * Determine if the user can manage administrators.
     */
    public function manageAdministrators(User $user): bool
    {
        return $user->hasPermission('manage_administrators');
    }

    /**
     * Determine if the user can manage events.
     */
    public function manageEvents(User $user): bool
    {
        return $user->hasPermission('manage_events');
    }

    /**
     * Determine if the user can manage clubs.
     */
    public function manageClubs(User $user): bool
    {
        return $user->hasPermission('manage_clubs');
    }

    /**
     * Determine if the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->hasPermission('view_reports');
    }

    /**
     * Determine if the user can manage permissions.
     */
    public function managePermissions(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can manage system settings.
     */
    public function manageSettings(User $user): bool
    {
        return $user->hasPermission('manage_settings');
    }

    /**
     * Determine if the user can view any user profile.
     */
    public function viewAny(User $user): bool
    {
        // Super admin and admin with manage_students permission can view all
        return $user->isSuperAdmin() || $user->hasPermission('manage_students');
    }

    /**
     * Determine if the user can view the user profile.
     */
    public function view(User $user, User $model): bool
    {
        // Can view own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super admin can view all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin with manage_students permission can view students/clubs
        if ($user->hasPermission('manage_students')) {
            return in_array($model->role, ['student', 'club', 'user']);
        }

        // Admin with manage_administrators permission can view admins
        if ($user->hasPermission('manage_administrators')) {
            return $model->role === 'admin';
        }

        return false;
    }

    /**
     * Determine if the user can update the user profile.
     */
    public function update(User $user, User $model): bool
    {
        // Can update own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super admin can update all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin with manage_students permission can update students/clubs
        if ($user->hasPermission('manage_students')) {
            return in_array($model->role, ['student', 'club', 'user']);
        }

        // Admin with manage_administrators permission can update admins
        if ($user->hasPermission('manage_administrators')) {
            return $model->role === 'admin' && !$model->isSuperAdmin();
        }

        return false;
    }
}
