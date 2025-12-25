<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Constants;

class PermissionConstants
{
    /**
     * User Management Module - 用户管理模块
     */
    public const USER_VIEW = 'view_users';
    public const USER_CREATE = 'create_user';
    public const USER_UPDATE = 'update_user';
    public const USER_DELETE = 'delete_user';
    public const USER_VIEW_DETAILS = 'view_user_details';
    public const USER_TOGGLE_STATUS = 'toggle_user_status';

    /**
     * Administrator Management Module - 管理员管理模块
     */
    public const ADMIN_VIEW = 'view_administrators';
    public const ADMIN_CREATE = 'create_administrator';
    public const ADMIN_UPDATE = 'update_administrator';
    public const ADMIN_DELETE = 'delete_administrator';
    public const ADMIN_VIEW_DETAILS = 'view_administrator_details';
    public const ADMIN_TOGGLE_STATUS = 'toggle_administrator_status';
    public const ADMIN_MANAGE_PERMISSIONS = 'manage_permissions';

    /**
     * Event Management Module - 事件管理模块
     */
    public const EVENT_MANAGE = 'manage_events';

    /**
     * Club Management Module - 社团管理模块
     */
    public const CLUB_MANAGE = 'manage_clubs';

    /**
     * Reports Module - 报告模块
     */
    public const VIEW_REPORTS = 'view_reports';

    /**
     * System Settings Module - 系统设置模块
     */
    public const MANAGE_SETTINGS = 'manage_settings';

    /**
     * Get all permissions grouped by module
     * 
     * @return array
     */
    public static function getAllPermissionsByModule(): array
    {
        return [
            'User Management' => [
                self::USER_VIEW => 'View Users List',
                self::USER_CREATE => 'Create User',
                self::USER_UPDATE => 'Update User',
                self::USER_DELETE => 'Delete User',
                self::USER_VIEW_DETAILS => 'View User Details',
                self::USER_TOGGLE_STATUS => 'Toggle User Status',
            ],
            'Administrator Management' => [
                self::ADMIN_VIEW => 'View Administrators List',
                self::ADMIN_CREATE => 'Create Administrator',
                self::ADMIN_UPDATE => 'Update Administrator',
                self::ADMIN_DELETE => 'Delete Administrator',
                self::ADMIN_VIEW_DETAILS => 'View Administrator Details',
                self::ADMIN_TOGGLE_STATUS => 'Toggle Administrator Status',
                self::ADMIN_MANAGE_PERMISSIONS => 'Manage Permissions',
            ],
            'Event Management' => [
                self::EVENT_MANAGE => 'Manage Events',
            ],
            'Club Management' => [
                self::CLUB_MANAGE => 'Manage Clubs',
            ],
            'Reports' => [
                self::VIEW_REPORTS => 'View Reports',
            ],
            'System Settings' => [
                self::MANAGE_SETTINGS => 'Manage System Settings',
            ],
        ];
    }

    /**
     * Get all permissions as flat array
     * 
     * @return array
     */
    public static function getAllPermissions(): array
    {
        $permissions = [];
        foreach (self::getAllPermissionsByModule() as $modulePermissions) {
            $permissions = array_merge($permissions, $modulePermissions);
        }
        return $permissions;
    }

    /**
     * Get permission keys only
     * 
     * @return array
     */
    public static function getPermissionKeys(): array
    {
        return array_keys(self::getAllPermissions());
    }

    /**
     * Check if a permission exists
     * 
     * @param string $permission
     * @return bool
     */
    public static function exists(string $permission): bool
    {
        return in_array($permission, self::getPermissionKeys());
    }
}

