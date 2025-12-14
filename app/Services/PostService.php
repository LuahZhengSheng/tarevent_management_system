<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Decorators\BasePostDecorator;
use App\Decorators\TagsPostDecorator;
use App\Decorators\MediaPostDecorator;
use App\Decorators\ValidationPostDecorator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PostService
{
    protected $postDecorator;

    public function __construct()
    {
        // Build decorator chain: Validation -> Media -> Tags -> Base
        $this->postDecorator = new ValidationPostDecorator(
            new MediaPostDecorator(
                new TagsPostDecorator(
                    new BasePostDecorator()
                )
            )
        );
    }

    /**
     * Create a new post using decorator pattern
     */
    public function createPost(array $data): Post
    {
        return $this->postDecorator->create($data);
    }

    /**
     * Update a post using decorator pattern
     */
    public function updatePost(Post $post, array $data): Post
    {
        return $this->postDecorator->update($post, $data);
    }

    /**
     * Get user's posts with pagination
     */
    public function getUserPosts(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Post::where('user_id', $user->id)
            ->with(['user', 'club'])
            ->withCount(['comments', 'likes'])
            ->recent()
            ->paginate($perPage);
    }

    /**
     * Get user's draft posts
     */
    public function getUserDrafts(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Post::where('user_id', $user->id)
            ->draft()
            ->with(['user', 'club'])
            ->recent()
            ->paginate($perPage);
    }

    /**
     * Get user's published posts
     */
    public function getUserPublishedPosts(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Post::where('user_id', $user->id)
            ->published()
            ->with(['user', 'club'])
            ->withCount(['comments', 'likes'])
            ->recent()
            ->paginate($perPage);
    }

    /**
     * Get post statistics for a user
     */
    public function getUserPostStats(User $user): array
    {
        return Cache::remember("user_post_stats_{$user->id}", 300, function () use ($user) {
            $posts = Post::where('user_id', $user->id);

            return [
                'total_posts' => $posts->count(),
                'published_posts' => $posts->clone()->published()->count(),
                'draft_posts' => $posts->clone()->draft()->count(),
                'total_views' => $posts->sum('views_count'),
                'total_likes' => $posts->sum('likes_count'),
                'total_comments' => $posts->sum('comments_count'),
            ];
        });
    }

    /**
     * Delete a post and its associated media files
     */
    public function deletePost(Post $post): bool
    {
        // Delete associated media files from storage
        if ($post->hasMedia()) {
            foreach ($post->media_paths as $media) {
                // media_paths 是数组，每个元素包含 path, type, mime_type 等
                if (isset($media['path']) && Storage::disk('public')->exists($media['path'])) {
                    Storage::disk('public')->delete($media['path']);
                }
            }
        }

        // Clear user stats cache
        Cache::forget("user_post_stats_{$post->user_id}");

        return $post->delete();
    }

    /**
     * Toggle post status between draft and published
     */
    public function togglePostStatus(Post $post): Post
    {
        $newStatus = $post->status === 'draft' ? 'published' : 'draft';
        $post->update(['status' => $newStatus]);
        
        // Clear cache
        Cache::forget("user_post_stats_{$post->user_id}");
        
        return $post->fresh();
    }

    /**
     * Get post categories
     */
    public function getCategories(): array
    {
        return [
            'Campus Life',
            'Academic',
            'Announcements',
            'Social',
            'Career',
            'Technology',
        ];
    }

    /**
     * Search posts
     */
    public function searchPosts(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $posts = Post::published()
            ->public()
            ->with(['user', 'club'])
            ->withCount(['comments', 'likes']);

        // Search in title and content
        if (!empty($query)) {
            $posts->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            });
        }

        // Filter by category
        if (!empty($filters['category'])) {
            $posts->byCategory($filters['category']);
        }

        // Filter by club
        if (!empty($filters['club_id'])) {
            $posts->where('club_id', $filters['club_id']);
        }

        // Sort by
        $sortBy = $filters['sort'] ?? 'recent';
        if ($sortBy === 'popular') {
            $posts->popular();
        } else {
            $posts->recent();
        }

        return $posts->paginate($perPage);
    }

    /**
     * Get featured posts
     */
    public function getFeaturedPosts(int $limit = 5)
    {
        return Post::published()
            ->public()
            ->with(['user', 'club'])
            ->withCount(['comments', 'likes'])
            ->popular()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent posts
     */
    public function getRecentPosts(int $limit = 10)
    {
        return Post::published()
            ->public()
            ->with(['user', 'club'])
            ->withCount(['comments', 'likes'])
            ->recent()
            ->limit($limit)
            ->get();
    }

    /**
     * Get posts by category
     */
    public function getPostsByCategory(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return Post::published()
            ->public()
            ->byCategory($category)
            ->with(['user', 'club'])
            ->withCount(['comments', 'likes'])
            ->recent()
            ->paginate($perPage);
    }

    /**
     * Get posts by club
     */
    public function getPostsByClub(int $clubId, int $perPage = 15): LengthAwarePaginator
    {
        return Post::published()
            ->where('club_id', $clubId)
            ->with(['user', 'club'])
            ->withCount(['comments', 'likes'])
            ->recent()
            ->paginate($perPage);
    }

    /**
     * Increment post views
     */
    public function incrementViews(Post $post): void
    {
        $post->incrementViews();
        
        // Clear cache
        Cache::forget("user_post_stats_{$post->user_id}");
    }

    /**
     * Clear user post stats cache
     */
    public function clearUserStatsCache(int $userId): void
    {
        Cache::forget("user_post_stats_{$userId}");
    }
}
