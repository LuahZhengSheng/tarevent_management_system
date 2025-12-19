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
//use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail {

    use HasFactory,
        Notifiable;

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
                        ->withPivot('role')
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
        return $this->role === 'user';
    }

    public function isClub(): bool {
        return $this->role === 'club';
    }

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool {
        return $this->role === 'super_admin';
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
    // Permission checking (for admin users)
    // =============================

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

    // =============================
    // Event permission checking
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
            'student' => 'images/avatar/default-student-avatar.png',
            'club' => 'images/avatar/default-student-avatar.png', // 暂时使用 student 默认头像
            'admin' => 'images/avatar/default-student-avatar.png', // 暂时使用 student 默认头像
            'super_admin' => 'images/avatar/default-student-avatar.png', // 暂时使用 student 默认头像
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

    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, $role) {
        return $query->where('role', $role);
    }

    public function scopeClubs($query) {
        return $query->where('role', 'club');
    }

    public function scopeStudents($query) {
        return $query->where('role', 'student');
    }

    // =============================
    // Methods
    // =============================

    public function updateLastLogin() {
        $this->update(['last_login_at' => now()]);
    }

    public function suspend(string $reason = null) {
        $this->update([
            'status' => 'suspended',
            'suspended_reason' => $reason,
        ]);
    }

    public function activate() {
        $this->update([
            'status' => 'active',
            'suspended_reason' => null,
        ]);
    }

    /**
     * Check if user can create posts
     */
    public function canCreatePost(): bool {
        return $this->isActive();
    }

    /**
     * Get user's post statistics
     * ✅ 修复：现在使用正确的 user_id 外键
     */
    public function getPostStatsAttribute(): array {
        return [
            'total_posts' => $this->posts()->count(),
            'published_posts' => $this->posts()->published()->count(),
            'draft_posts' => $this->posts()->draft()->count(),
            'total_likes' => $this->posts()->sum('likes_count'),
            'total_comments' => $this->posts()->sum('comments_count'),
        ];
    }
    
    /*
     * Notification helpers
     */
    // Get unread notifications count
    public function getUnreadNotificationsCountAttribute() {
        return $this->notifications()->unread()->count();
    }

    // Get recent unread notifications
    public function getRecentUnreadNotificationsAttribute() {
        return $this->notifications()
                        ->unread()
                        ->recent()
                        ->limit(5)
                        ->get();
    }

    // Mark all notifications as read
    public function markAllNotificationsAsRead() {
        return $this->notifications()
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);
    }

    /**
     * Event subscription helpers
     */
    // Check if user is subscribed to an event
    public function isSubscribedToEvent($eventId) {
        return $this->eventSubscriptions()
                        ->where('event_id', $eventId)
                        ->where('is_active', true)
                        ->exists();
    }

    // Subscribe to event
    public function subscribeToEvent($eventId) {
        return EventSubscription::subscribe($this->id, $eventId);
    }

    // Unsubscribe from event
    public function unsubscribeFromEvent($eventId, $reason = null) {
        EventSubscription::unsubscribeFromEvent($this->id, $eventId, $reason);
    }
}
