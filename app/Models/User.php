<?php

/**
 * Author: Tang Lit Xuan
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
// Import all traits
use App\Models\Traits\HasRoles;
use App\Models\Traits\HasPermissions;
use App\Models\Traits\HasProfilePhoto;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasNotifications;
use App\Models\Traits\HasEventSubscriptions;
use App\Models\Traits\HasEventPermissions;
use App\Models\Traits\HasForumActivity;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PostSave;
use App\Models\PostComment;
use App\Models\PostLike;

class User extends Authenticatable implements MustVerifyEmail {

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

    /**
     * User's post saves / bookmarks
     */
    public function postSaves() {
        return $this->hasMany(PostSave::class, 'user_id');
    }

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
