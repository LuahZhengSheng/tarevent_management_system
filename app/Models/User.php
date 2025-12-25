<?php

/**
 * Author: Tang Lit Xuan
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

// Import all traits
use App\Models\Traits\HasRoles;
use App\Models\Traits\HasPermissions;
use App\Models\Traits\HasProfilePhoto;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasNotifications;
use App\Models\Traits\HasEventSubscriptions;
use App\Models\Traits\HasEventPermissions;
use App\Models\Traits\HasForumActivity;
use App\Models\PostSave;
use App\Models\PostComment;
use App\Models\PostLike;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles,
        HasPermissions,
        HasProfilePhoto,
        HasStatus,
        HasNotifications,
        HasEventSubscriptions,
        HasEventPermissions,
        HasForumActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // student / club / admin / super_admin
        'club_id', // If user is club admin
        'profile_photo', // 存储路径如: avatars/abc123.jpg
        'phone',
        'program',
        'student_id',
        'interested_categories', // JSON array
        'email_verified_at',
        'status', // active / inactive / suspended
        'last_login_at',
        'permissions', // JSON array of permissions for admin users
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'interested_categories' => 'array',
        'permissions' => 'array',
    ];
    protected $attributes = [
        'role' => 'student',
        'status' => 'active',
    ];

    // =============================
    // Relationships
    // =============================

    public function club() {
        return $this->belongsTo(Club::class);
    }

    // 学生加入的 clubs（通过 club_user 中间表）
    public function clubs() {
        return $this->belongsToMany(Club::class, 'club_user')
                        ->withPivot('role', 'status')
                        ->withTimestamps();
    }

    // 被加入黑名单的俱乐部
    public function blacklistedClubs() {
        return $this->belongsToMany(Club::class, 'club_blacklist')
                    ->withPivot('reason', 'blacklisted_by')
                    ->withTimestamps();
    }

    // 判断是否是某个 club 的成员
    public function isMemberOfClub($clubId): bool {
        return $this->clubs()
                        ->where('clubs.id', $clubId)
                        ->exists();
    }

    public function eventRegistrations() {
        return $this->hasMany(EventRegistration::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

//    public function posts() {
//        return $this->hasMany(Post::class);
//    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    // Notifications relationship
    public function notifications() {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    // Event subscriptions relationship
    public function eventSubscriptions() {
        return $this->hasMany(EventSubscription::class);
    }

    // Active event subscriptions
    public function activeEventSubscriptions() {
        return $this->hasMany(EventSubscription::class)->where('is_active', true);
    }

    // Subscribed events
    public function subscribedEvents() {
        return $this->belongsToMany(Event::class, 'event_subscriptions')
                        ->wherePivot('is_active', true)
                        ->withPivot('subscribed_at', 'is_active')
                        ->withTimestamps();
    }

    /**
     * Relationship: User's forum posts
     * ✅ 修复：指定外键为 user_id
     */
    public function posts() {
        return $this->hasMany(Post::class, 'user_id');  // ← 添加 'user_id'
    }

    /**
     * Relationship: User's post comments
     */
    public function postComments() {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Relationship: User's post likes
     */
    public function postLikes() {
        return $this->hasMany(PostLike::class);
    }

    /**
     * User's post saves / bookmarks
     */
    public function postSaves() {
        return $this->hasMany(PostSave::class, 'user_id');
    }

    // =============================
    // Role checking methods
    // =============================

    public function hasRole(string $role): bool {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool {
        return in_array($this->role, $roles);
    }

    public function isStudent(): bool {
        return $this->role === 'student';
    }

    public function isClub(): bool {
        return $this->role === 'club';
    }

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    public function isClubAdmin(int $clubId = null): bool {
        if (!$this->isClub()) {
            return false;
        }

        if ($clubId === null) {
            return $this->club_id !== null;
        }

        return $this->club_id === $clubId;
    }

    // =============================
    // Status checking methods
    // =============================

    public function isActive(): bool {
        return $this->status === 'active';
    }

    public function isSuspended(): bool {
        return $this->status === 'suspended';
    }

    // =============================
    // Permission checking
    // =============================

    public function canCreateEvent(): bool {
        return $this->isClub() || $this->isAdmin();
    }

    public function canEditEvent(Event $event): bool {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isClub() && $event->organizer_type === 'club') {
            return $event->organizer_id === $this->club_id;
        }

        return false;
    }

    public function canDeleteEvent(Event $event): bool {
        return $this->canEditEvent($event);
    }

    public function canRegisterForEvent(Event $event): bool {
        // Check if user is active
        if (!$this->isActive()) {
            return false;
        }

        // Check if already registered
        if ($this->isRegisteredForEvent($event)) {
            return false;
        }

        // Check if event is open for registration
        return $event->is_registration_open;
    }

    public function isRegisteredForEvent(Event $event): bool {
        return $this->eventRegistrations()
                        ->where('event_id', $event->id)
                        ->whereIn('status', ['confirmed', 'pending_payment'])
                        ->exists();
    }

    // =============================
    // Accessors
    // =============================

    /**
     * Get the user's profile photo URL
     * 
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        // 如果有上传的头像且文件存在
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return asset('storage/' . $this->profile_photo);
        }

        // 返回默认头像（基于角色）
        $defaultAvatars = [
            'student' => 'images/default-student-avatar.png',
            'club' => 'images/default-club-avatar.png',
            'admin' => 'images/default-admin-avatar.png',
        ];

        return asset($defaultAvatars[$this->role] ?? $defaultAvatars['student']);
    }

    /**
     * Get the storage path for profile photo
     * 
     * @return string|null
     */
    public function getProfilePhotoPathAttribute()
    {
        if ($this->profile_photo) {
            return storage_path('app/public/' . $this->profile_photo);
        }
        
        return null;
    }

    /**
     * Check if user has uploaded profile photo
     * 
     * @return bool
     */
    public function hasProfilePhoto(): bool
    {
        return $this->profile_photo && Storage::disk('public')->exists($this->profile_photo);
    }

    /**
     * Delete user's profile photo
     * 
     * @return bool
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
    
//    public function getUnreadNotificationsCountAttribute()
//    {
//        return $this->notifications()
//                    ->whereNull('read_at')
//                    ->count();
//    }

    // =============================
    // Scopes
    // =============================

    public function scopeByRole($query, $role) {
        return $query->where('role', $role);
    }

    public function scopeClubs($query) {
        return $query->where('role', 'club');
    }

    public function scopeStudents($query) {
        return $query->where('role', 'student');
    }
}
