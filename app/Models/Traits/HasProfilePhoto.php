<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait HasProfilePhoto
{
    /**
     * Get the user's profile photo URL
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        // 如果有上传的头像且文件存在
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return asset('storage/' . $this->profile_photo);
        }

        // 返回默认头像（基于角色）
        $defaultAvatars = [
            'student' => 'images/avatar/default-student-avatar.png',
            'club' => 'images/avatar/default-student-avatar.png',
            'admin' => 'images/avatar/default-student-avatar.png',
            'super_admin' => 'images/avatar/default-student-avatar.png',
        ];

        return asset($defaultAvatars[$this->role] ?? $defaultAvatars['student']);
    }

    /**
     * Get the storage path for profile photo
     */
    public function getProfilePhotoPathAttribute(): ?string
    {
        if ($this->profile_photo) {
            return storage_path('app/public/' . $this->profile_photo);
        }
        
        return null;
    }

    /**
     * Check if user has uploaded profile photo
     */
    public function hasProfilePhoto(): bool
    {
        return $this->profile_photo && Storage::disk('public')->exists($this->profile_photo);
    }

    /**
     * Delete user's profile photo
     */
    public function deleteProfilePhoto(): bool
    {
        if ($this->hasProfilePhoto()) {
            Storage::disk('public')->delete($this->profile_photo);
            $this->profile_photo = null;
            $this->save();
            return true;
        }
        
        return false;
    }
}

