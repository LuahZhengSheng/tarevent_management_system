<?php

namespace App\Models;

/**
 * Club Member Role Constants
 * 
 * Defines standard roles for club members.
 * This class provides constants and helper methods for club member roles.
 */
class ClubMemberRole
{
    // Standard Club Member Roles
    public const MEMBER = 'member';
    public const PRESIDENT = 'president';
    public const VICE_PRESIDENT = 'vice_president';
    public const SECRETARY = 'secretary';
    public const TREASURER = 'treasurer';
    public const OFFICER = 'officer';
    public const COMMITTEE_MEMBER = 'committee_member';

    /**
     * Get all available roles.
     * 
     * @return array
     */
    public static function all(): array
    {
        return [
            self::MEMBER,
            self::PRESIDENT,
            self::VICE_PRESIDENT,
            self::SECRETARY,
            self::TREASURER,
            self::OFFICER,
            self::COMMITTEE_MEMBER,
        ];
    }

    /**
     * Get role display name.
     * 
     * @param string $role
     * @return string
     */
    public static function displayName(string $role): string
    {
        return match($role) {
            self::MEMBER => 'Member',
            self::PRESIDENT => 'President',
            self::VICE_PRESIDENT => 'Vice President',
            self::SECRETARY => 'Secretary',
            self::TREASURER => 'Treasurer',
            self::OFFICER => 'Officer',
            self::COMMITTEE_MEMBER => 'Committee Member',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }

    /**
     * Get role description.
     * 
     * @param string $role
     * @return string
     */
    public static function description(string $role): string
    {
        return match($role) {
            self::MEMBER => 'Regular club member',
            self::PRESIDENT => 'Club president, highest authority',
            self::VICE_PRESIDENT => 'Vice president, assists president',
            self::SECRETARY => 'Handles club documentation and records',
            self::TREASURER => 'Manages club finances',
            self::OFFICER => 'Club officer with administrative duties',
            self::COMMITTEE_MEMBER => 'Member of club committee',
            default => 'Club member role',
        };
    }

    /**
     * Check if a role is valid.
     * 
     * @param string $role
     * @return bool
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::all());
    }

    /**
     * Get executive roles (roles with higher authority).
     * 
     * @return array
     */
    public static function executiveRoles(): array
    {
        return [
            self::PRESIDENT,
            self::VICE_PRESIDENT,
            self::SECRETARY,
            self::TREASURER,
        ];
    }

    /**
     * Check if a role is an executive role.
     * 
     * @param string $role
     * @return bool
     */
    public static function isExecutive(string $role): bool
    {
        return in_array($role, self::executiveRoles());
    }

    /**
     * Get roles with management permissions.
     * 
     * @return array
     */
    public static function managementRoles(): array
    {
        return [
            self::PRESIDENT,
            self::VICE_PRESIDENT,
            self::SECRETARY,
            self::TREASURER,
            self::OFFICER,
        ];
    }

    /**
     * Check if a role has management permissions.
     * 
     * @param string $role
     * @return bool
     */
    public static function canManage(string $role): bool
    {
        return in_array($role, self::managementRoles());
    }

    /**
     * Get role hierarchy (higher number = higher authority).
     * 
     * @param string $role
     * @return int
     */
    public static function hierarchy(string $role): int
    {
        return match($role) {
            self::PRESIDENT => 7,
            self::VICE_PRESIDENT => 6,
            self::SECRETARY => 5,
            self::TREASURER => 4,
            self::OFFICER => 3,
            self::COMMITTEE_MEMBER => 2,
            self::MEMBER => 1,
            default => 0,
        };
    }

    /**
     * Check if role1 has higher authority than role2.
     * 
     * @param string $role1
     * @param string $role2
     * @return bool
     */
    public static function hasHigherAuthority(string $role1, string $role2): bool
    {
        return self::hierarchy($role1) > self::hierarchy($role2);
    }
}

