<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CommentLike;
use App\Models\User;

class PostComment extends Model
{
    use SoftDeletes;

    protected $table = 'post_comments';

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'parent_id',
        'reply_to_user_id',
        'media_paths',
        'likes_count',
    ];

    protected $casts = [
        'media_paths' => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo
    {
        return $this->user();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PostComment::class, 'parent_id')->latest();
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_to_user_id');
    }

    /** 评论点赞关联（comments_likes） */
    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }

    /** 当前用户是否已点赞 */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()
            ->where('user_id', $user->id)
            ->exists();
    }

    // 给 blade 用：统一拿 media 列表（带 url/type）
    public function getMediaAttribute()
    {
        if (empty($this->media_paths) || !is_array($this->media_paths)) {
            return collect();
        }

        return collect($this->media_paths)->map(function ($m) {
            $path = is_array($m) ? ($m['path'] ?? null) : $m;
            $type = is_array($m) ? ($m['type'] ?? 'image') : 'image';
            $mime = is_array($m) ? ($m['mime_type'] ?? null) : null;

            return (object)[
                'path'      => $path,
                'type'      => $type,
                'mime_type' => $mime,
                'url'       => $path ? Storage::url($path) : null,
            ];
        })->filter(fn ($m) => !empty($m->path));
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $this->user_id === (int) $user->id;
    }
}
