<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostSave extends Model
{
    protected $table = 'post_saves';

    protected $fillable = [
        'user_id',
        'post_id',
        'pinned_at',
        'last_viewed_at',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
