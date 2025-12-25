<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Models\Traits;

trait HasForumActivity
{
    /**
     * Check if user can create posts
     */
    public function canCreatePost(): bool
    {
        return $this->isActive();
    }

    /**
     * Get user's post statistics
     */
    public function getPostStatsAttribute(): array
    {
        return [
            'total_posts' => $this->posts()->count(),
            'published_posts' => $this->posts()->published()->count(),
            'draft_posts' => $this->posts()->draft()->count(),
            'total_likes' => $this->posts()->sum('likes_count'),
            'total_comments' => $this->posts()->sum('comments_count'),
        ];
    }
}

