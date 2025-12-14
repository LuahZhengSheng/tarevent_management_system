<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model {

    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'status',
    ];

    // 一个 club 有很多 events（通过 polymorphic organizer）
    public function events() {
        return $this->morphMany(Event::class, 'organizer');
    }

    public function members() {
        return $this->belongsToMany(User::class, 'club_user')
                        ->withPivot('role')
                        ->withTimestamps();
    }
}
