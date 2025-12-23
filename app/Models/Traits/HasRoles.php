<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Models\Traits;

trait HasRoles
{
    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user is a club organizer
     */
    public function isClub(): bool
    {
        return $this->role === 'club';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is an administrator (admin or super_admin)
     */
    public function isAdministrator(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user is a club admin for a specific club
     */
    public function isClubAdmin(int $clubId = null): bool
    {
        if (!$this->isClub()) {
            return false;
        }

        if ($clubId === null) {
            return $this->club_id !== null;
        }

        return $this->club_id === $clubId;
    }
}

