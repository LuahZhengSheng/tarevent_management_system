<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AuthorizationService
{
    /**
     * Check if user can manage students
     */
    public function canManageStudents(): bool
    {
        return Gate::allows('manageStudents', User::class);
    }
    
    /**
     * Check if user can manage administrators
     */
    public function canManageAdministrators(): bool
    {
        return Gate::allows('manageAdministrators', User::class);
    }
    
    /**
     * Check if user can view another user
     */
    public function canView(User $user): bool
    {
        return Gate::allows('view', $user);
    }
    
    /**
     * Check if user can update another user
     */
    public function canUpdate(User $user): bool
    {
        return Gate::allows('update', $user);
    }
    
    /**
     * Authorize action or abort with 403
     * 
     * @param string $ability The ability to check (e.g., 'view', 'update')
     * @param mixed $arguments The arguments to pass to the policy
     * @param string|null $message Custom error message
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function authorizeOrAbort(string $ability, $arguments, ?string $message = null): void
    {
        if (!Gate::allows($ability, $arguments)) {
            abort(403, $message ?? 'You do not have permission to perform this action.');
        }
    }
    
    /**
     * Authorize managing students or abort
     */
    public function authorizeManageStudentsOrAbort(?string $message = null): void
    {
        $this->authorizeOrAbort('manageStudents', User::class, $message ?? 'You do not have permission to manage students.');
    }
    
    /**
     * Authorize managing administrators or abort
     */
    public function authorizeManageAdministratorsOrAbort(?string $message = null): void
    {
        $this->authorizeOrAbort('manageAdministrators', User::class, $message ?? 'You do not have permission to manage administrators.');
    }
    
    /**
     * Authorize viewing user or abort
     */
    public function authorizeViewOrAbort(User $user, ?string $message = null): void
    {
        $this->authorizeOrAbort('view', $user, $message ?? 'You do not have permission to view this user.');
    }
    
    /**
     * Authorize updating user or abort
     */
    public function authorizeUpdateOrAbort(User $user, ?string $message = null): void
    {
        $this->authorizeOrAbort('update', $user, $message ?? 'You do not have permission to update this user.');
    }
}

