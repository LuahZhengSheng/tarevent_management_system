<?php

namespace App\Services;

use App\Models\User;

class UserStatusService
{
    /**
     * Toggle user status between active and inactive
     */
    public function toggleStatus(User $user): string
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        
        return $newStatus;
    }
    
    /**
     * Set user status to a specific value
     * 
     * @throws \InvalidArgumentException if status is invalid
     */
    public function setStatus(User $user, string $status): void
    {
        $allowedStatuses = ['active', 'inactive', 'suspended'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}. Allowed values are: " . implode(', ', $allowedStatuses));
        }
        
        $user->update(['status' => $status]);
    }
    
    /**
     * Activate user
     */
    public function activate(User $user): void
    {
        $this->setStatus($user, 'active');
    }
    
    /**
     * Deactivate user
     */
    public function deactivate(User $user): void
    {
        $this->setStatus($user, 'inactive');
    }
    
    /**
     * Suspend user
     */
    public function suspend(User $user): void
    {
        $this->setStatus($user, 'suspended');
    }
}

