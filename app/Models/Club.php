<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Club extends Model {

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'email',
        'phone',
        'logo',
        'background_image',
        'status',
        'created_by',
        'approved_at',
        'approved_by',
        'club_user_id',
    ];
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // 一个 club 有很多 events（通过 polymorphic organizer）
    public function events() {
        return $this->morphMany(Event::class, 'organizer');
    }

    public function members() {
        return $this->belongsToMany(User::class, 'club_user')
                        ->withPivot('role', 'status')
                        ->withTimestamps();
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function clubUser() {
        return $this->belongsTo(User::class, 'club_user_id');
    }

    public function blacklist() {
        return $this->hasMany(ClubBlacklist::class);
    }

    public function blacklistedUsers() {
        return $this->belongsToMany(User::class, 'club_blacklist')
                    ->withPivot('reason', 'blacklisted_by')
                    ->withTimestamps();
    }

    public function announcements() {
        return $this->hasMany(ClubAnnouncement::class);
    }

    public function publishedAnnouncements() {
        return $this->hasMany(ClubAnnouncement::class)->published();
    }

    /**
     * Boot the model.
     */
    protected static function boot() {
        parent::boot();

        static::creating(function ($club) {
            // Automatically generate slug from name if not provided
            if (empty($club->slug) && !empty($club->name)) {
                $club->slug = Str::slug($club->name);
            }
        });
    }

    public function posts() {
        return $this->belongsToMany(Post::class, 'club_posts')
                        ->withPivot(['pinned', 'status'])
                        ->withTimestamps();
    }
}
