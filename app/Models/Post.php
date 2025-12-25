<?php

// app/Models/Post.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Post extends Model {

    use HasFactory,
        SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'visibility',
        'status',
        'media_paths',
        'views_count',
        'likes_count',
        'comments_count',
        'published_at',
    ];
    protected $casts = [
        'media_paths' => 'array',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    protected $attributes = [
        'status' => 'published',
        'visibility' => 'public',
        'views_count' => 0,
        'likes_count' => 0,
        'comments_count' => 0,
    ];

    // =============================
    // Relationships
    // =============================

    /**
     * Post belongs to a user (author)
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user()
     */
    public function author() {
        return $this->user();
    }

    /**
     * Post belongs to a category
     */
    public function category() {
        return $this->belongsTo(Category::class);
    }

    /**
     * Post has many tags (many-to-many with pivot)
     */
    public function tags() {
        return $this->belongsToMany(Tag::class, 'post_tag')
                        ->withPivot(['tagged_by', 'source', 'order', 'is_confirmed'])
                        ->withTimestamps()
                        ->orderByPivot('order', 'asc');
    }

    public function clubs() {
        return $this->belongsToMany(Club::class, 'club_posts')
                        ->withPivot(['pinned', 'status'])
                        ->withTimestamps();
    }

    /**
     * Post has many comments
     */
    public function comments() {
        return $this->hasMany(PostComment::class, 'post_id');
    }

    /**
     * Post has many likes
     */
    public function likes() {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    // =============================
    // Media Helper Methods
    // =============================

    /**
     * Get all media files
     */
    public function getMediaAttribute() {
        if (!$this->media_paths || !is_array($this->media_paths)) {
            return collect([]);
        }

        return collect($this->media_paths)->map(function ($media) {
                    return (object) [
                        'type' => $media['type'] ?? 'image',
                        'path' => $media['path'] ?? '',
                        'mime_type' => $media['mime_type'] ?? '',
                        'size' => $media['size'] ?? 0,
                        'original_name' => $media['original_name'] ?? '',
                        'url' => isset($media['path']) ? Storage::url($media['path']) : '',
                    ];
                });
    }

    /**
     * Get only images
     */
    public function getImagesAttribute() {
        return $this->media->where('type', 'image');
    }

    /**
     * Get only videos
     */
    public function getVideosAttribute() {
        return $this->media->where('type', 'video');
    }

    /**
     * Check if post has media
     */
    public function hasMedia() {
        return !empty($this->media_paths) && is_array($this->media_paths) && count($this->media_paths) > 0;
    }

    /**
     * Add media files to post
     */
    public function addMedia(array $mediaFiles) {
        $currentMedia = $this->media_paths ?? [];
        $this->media_paths = array_merge($currentMedia, $mediaFiles);
        $this->save();
    }

    /**
     * Replace all media files
     */
    public function replaceMedia(array $mediaFiles) {
        $this->deleteMediaFiles();
        $this->media_paths = $mediaFiles;
        $this->save();
    }

    /**
     * Delete all media files from storage
     */
    public function deleteMediaFiles() {
        if (!$this->hasMedia()) {
            return;
        }

        foreach ($this->media_paths as $media) {
            if (!is_array($media)) {
                continue;
            }

            $disk = $media['disk'] ?? 'public';  // 没有 disk 当 public
            $path = $media['path'] ?? null;

            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    /**
     * Get first media file
     */
    public function getFirstMediaAttribute() {
        return $this->media->first();
    }

    /**
     * Get media count
     */
    public function getMediaCountAttribute() {
        return $this->media->count();
    }

    // =============================
    // Tag Helper Methods
    // =============================

    /**
     * Sync tags with usage count tracking
     */
    public function syncTagsWithCount(array $tagIds, $userId = null) {
        // Get old tags
        $oldTagIds = $this->tags()->pluck('tags.id')->toArray();

        // Prepare data with metadata
        $syncData = [];
        foreach ($tagIds as $index => $tagId) {
            $syncData[$tagId] = [
                'tagged_by' => $userId ?? auth()->id(),
                'source' => 'user_manual',
                'order' => $index,
                'is_confirmed' => true,
            ];
        }

        // Sync tags
        $this->tags()->sync($syncData);

        // Update usage counts for removed tags
        $removedTagIds = array_diff($oldTagIds, $tagIds);
        if (!empty($removedTagIds)) {
            Tag::whereIn('id', $removedTagIds)->each(function ($tag) {
                $tag->decrementUsage();
            });
        }

        // Update usage counts for new tags
        $newTagIds = array_diff($tagIds, $oldTagIds);
        if (!empty($newTagIds)) {
            Tag::whereIn('id', $newTagIds)->each(function ($tag) {
                $tag->incrementUsage();
            });
        }
    }

    /**
     * Attach tags by names (create if not exist)
     */
    public function attachTagsByNames(array $tagNames, $userId = null) {
        $tagIds = [];

        foreach ($tagNames as $index => $tagName) {
            $tagName = strtolower(trim($tagName));

            if (empty($tagName)) {
                continue;
            }

            // Find or create tag
            $tag = Tag::firstOrCreate(
                            ['name' => $tagName],
                            [
                                'slug' => Str::slug($tagName),
                                'status' => 'pending',
                                'type' => 'community',
                                'created_by' => $userId ?? auth()->id(),
                            ]
            );

            $tagIds[] = $tag->id;
        }

        // Sync tags with count update
        if (!empty($tagIds)) {
            $this->syncTagsWithCount($tagIds, $userId);
        }
    }

    /**
     * Reorder tags
     */
    public function reorderTags(array $tagIds) {
        foreach ($tagIds as $order => $tagId) {
            $this->tags()->updateExistingPivot($tagId, ['order' => $order]);
        }
    }

    /**
     * Confirm a suggested tag
     */
    public function confirmTag($tagId) {
        $this->tags()->updateExistingPivot($tagId, [
            'is_confirmed' => true,
            'source' => 'user_manual',
        ]);
    }

    /**
     * Get tag names as array
     */
    public function getTagNamesAttribute() {
        return $this->tags->pluck('name')->toArray();
    }

    /**
     * Get only confirmed tags
     */
    public function getConfirmedTagsAttribute() {
        return $this->tags()->wherePivot('is_confirmed', true)->get();
    }

    /**
     * Get only suggested (unconfirmed) tags
     */
    public function getSuggestedTagsAttribute() {
        return $this->tags()->wherePivot('is_confirmed', false)->get();
    }

    // =============================
    // Scopes
    // =============================

    /**
     * Scope: Only published posts
     */
    public function scopePublished($query) {
        return $query->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
    }

    /**
     * Scope: Only draft posts
     */
    public function scopeDraft($query) {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Only public posts
     */
    public function scopePublic($query) {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, $categoryId) {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Filter by tag
     */
    public function scopeByTag($query, $tagId) {
        return $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
    }

    /**
     * Scope: Filter by multiple tags (AND logic)
     */
    public function scopeByTags($query, array $tagIds) {
        foreach ($tagIds as $tagId) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }
        return $query;
    }

    /**
     * Scope: Filter by any of the tags (OR logic)
     */
    public function scopeByAnyTag($query, array $tagIds) {
        return $query->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                });
    }

    /**
     * Scope: Search by title or content
     */
    public function scopeSearch($query, $search) {
        return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                });
    }

    /**
     * Scope: Order by recent
     */
    public function scopeRecent($query) {
        return $query->orderBy('published_at', 'desc')
                        ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Order by popular (likes + comments + views)
     */
    public function scopePopular($query) {
        return $query->orderByRaw('(likes_count * 3 + comments_count * 2 + views_count) DESC');
    }

    /**
     * Scope: With full relationships
     */
    public function scopeWithFullRelations($query) {
        return $query->with(['user', 'category', 'tags', 'clubs']);
    }

    /**
     * Scope: Visible to user
     */
    public function scopeVisibleTo($query, ?User $user = null) {
        return $query->where(function ($q) use ($user) {
                    $q->where('visibility', 'public')
                            ->orWhere(function ($subQ) use ($user) {
                                if ($user) {
                                    $subQ->where('visibility', 'club_only')
                                    ->where('club_id', $user->club_id);
                                }
                            });
                });
    }

    // =============================
    // Accessors
    // =============================

    /**
     * Get post excerpt
     */
    public function getExcerptAttribute() {
        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get custom excerpt with length
     */
    public function excerpt($length = 150) {
        return Str::limit(strip_tags($this->content), $length);
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute() {
        return $this->published_at ? $this->published_at->format('M d, Y') : $this->created_at->format('M d, Y');
    }

    /**
     * Get reading time in minutes
     */
    public function getReadTimeAttribute() {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200); // Average reading speed: 200 words/min
        return $minutes . ' min read';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute() {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get visibility badge color
     */
    public function getVisibilityColorAttribute() {
        return match ($this->visibility) {
            'public' => 'primary',
            'club_only' => 'warning',
            default => 'secondary',
        };
    }

    // =============================
    // Methods
    // =============================

    /**
     * Check if user can edit this post
     */
    public function canBeEditedBy(?User $user) {
        if (!$user)
            return false;

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('clubs') && $this->club_id === $user->club_id) {
            return true;
        }

        return $this->user_id === $user->id;
    }

    /**
     * Check if user can view this post
     */
    public function canBeViewedBy(?User $user): bool {
        // drafts: only author/admin
        if ($this->status !== 'published') {
            return $this->canBeEditedBy($user);
        }

        if ($this->visibility === 'public') {
            return true;
        }

        if ($this->visibility === 'club_only') {
            if (!$user)
                return false;
            if ($user->hasRole('admin'))
                return true;

            // user clubs (club_user) ∩ post clubs (club_posts)
            return $this->clubs()
                            ->whereIn('clubs.id', $user->clubs()->select('clubs.id'))
                            ->exists();
        }

        return false;
    }

    /**
     * Increment post views
     */
    public function incrementViews() {
        $this->increment('views_count');
    }

    /**
     * Toggle like for user
     */
    public function toggleLike(User $user) {
        $like = $this->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $this->decrement('likes_count');
            return false; // unliked
        } else {
            $this->likes()->create(['user_id' => $user->id]);
            $this->increment('likes_count');
            return true; // liked
        }
    }

    /**
     * Check if user has liked this post
     */
    public function isLikedBy(?User $user) {
        if (!$user)
            return false;

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Publish the post
     */
    public function publish() {
        $wasPublished = $this->status === 'published';

        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);

        // Increment category post count if newly published
        if (!$wasPublished && $this->category) {
            $this->category->incrementPostCount();
        }

        return $this;
    }

    /**
     * Save as draft
     */
    public function saveDraft() {
        $wasPublished = $this->status === 'published';

        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Decrement category post count if was published
        if ($wasPublished && $this->category) {
            $this->category->decrementPostCount();
        }

        return $this;
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName() {
        return 'slug';
    }

    // =============================
    // Static Methods
    // =============================

    /**
     * Get popular posts
     */
    public static function getPopular($limit = 5) {
        return static::published()
                        ->public()
                        ->popular()
                        ->limit($limit)
                        ->get();
    }

    /**
     * Get recent posts
     */
    public static function getRecent($limit = 10) {
        return static::published()
                        ->public()
                        ->recent()
                        ->limit($limit)
                        ->get();
    }

    // =============================
    // Boot
    // =============================

    protected static function boot() {
        parent::boot();

        // Auto-generate slug when creating
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);

                // Ensure uniqueness
                $originalSlug = $post->slug;
                $count = 1;
                while (static::where('slug', $post->slug)->exists()) {
                    $post->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }

            // Set published_at if publishing
            if ($post->status === 'published' && !$post->published_at) {
                $post->published_at = now();
            }
        });

        // Update slug if title changed
        static::updating(function ($post) {
            // Set published_at when publishing
            if ($post->isDirty('status') && $post->status === 'published' && !$post->published_at) {
                $post->published_at = now();
            }

            // Clear published_at when saving as draft
            if ($post->isDirty('status') && $post->status === 'draft') {
                $post->published_at = null;
            }
        });

        // Handle post creation
        static::created(function ($post) {
            // Increment category count if published
            if ($post->status === 'published' && $post->category) {
                $post->category->incrementPostCount();
            }
        });

        // Handle post deletion
        static::deleting(function ($post) {
            // Delete media files
            $post->deleteMediaFiles();

            // Update category count
            if ($post->status === 'published' && $post->category) {
                $post->category->decrementPostCount();
            }

            // Update tag usage counts and detach
            $tagIds = $post->tags()->pluck('tags.id')->toArray();
            if (!empty($tagIds)) {
                Tag::whereIn('id', $tagIds)->each(function ($tag) {
                    $tag->decrementUsage();
                });
                $post->tags()->detach();
            }

            // Delete related comments and likes (cascade should handle this)
            // But just in case soft delete doesn't trigger cascade:
            $post->comments()->delete();
            $post->likes()->delete();
        });
    }

    public function savedByUsers() {
        return $this->belongsToMany(User::class, 'post_saves')
                        ->withPivot(['pinned_at', 'last_viewed_at'])
                        ->withTimestamps();
    }

    public function saves() {
        return $this->hasMany(\App\Models\PostSave::class, 'post_id');
    }
}
