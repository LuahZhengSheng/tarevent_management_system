<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    /**
     * Upload and set avatar for user
     */
    public function uploadAvatar(User $user, UploadedFile $file): string
    {
        // Delete old avatar if exists
        $this->deleteAvatar($user);

        // Store new avatar
        $path = $file->store('avatars', 'public');
        $user->profile_photo = $path;
        $user->save();

        return $path;
    }

    /**
     * Delete user's avatar
     */
    public function deleteAvatar(User $user): void
    {
        $user->deleteProfilePhoto();
    }

    /**
     * Check if user has avatar
     */
    public function hasAvatar(User $user): bool
    {
        return $user->hasProfilePhoto();
    }
}

