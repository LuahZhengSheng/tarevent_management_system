<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Services;

class ProfileRouteService
{
    /**
     * Check if current route is admin profile route
     */
    public function isAdminProfileRoute(): bool
    {
        return request()->routeIs('admin.profile.*');
    }
    
    /**
     * Get profile edit view name based on current route
     */
    public function getEditViewName(): string
    {
        return $this->isAdminProfileRoute() 
            ? 'admin.profile.edit' 
            : 'profile.edit';
    }
    
    /**
     * Get profile edit route name based on current route
     */
    public function getEditRouteName(): string
    {
        return $this->isAdminProfileRoute() 
            ? 'admin.profile.edit' 
            : 'profile.edit';
    }
}

