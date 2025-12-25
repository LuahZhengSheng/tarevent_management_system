<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Models\Traits;

trait HasPermissions
{
    /**
     * Check if user has a specific permission
     * Super admin always returns true
     * Admin users check their permissions array
     * If permissions is null, admin can only manage own profile
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Only admin users have permissions
        if (!$this->isAdmin()) {
            return false;
        }

        // If permissions is null, admin can only manage own profile
        if ($this->permissions === null) {
            return false;
        }

        // Check if permission exists in permissions array
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user can only manage their own profile
     * Returns true if admin has null permissions
     */
    public function canOnlyManageOwnProfile(): bool
    {
        return $this->isAdmin() && !$this->isSuperAdmin() && $this->permissions === null;
    }
}

