<?php

namespace App\Support;

use Illuminate\Support\Str;

class PostHelper
{
    /**
     * Generate excerpt from content
     */
    public static function generateExcerpt(string $content, int $length = 150): string
    {
        $text = strip_tags($content);
        return Str::limit($text, $length);
    }

    /**
     * Calculate reading time
     */
    public static function calculateReadTime(string $content): string
    {
        $words = str_word_count(strip_tags($content));
        $minutes = ceil($words / 200); // Average reading speed: 200 words/min
        
        if ($minutes < 1) {
            return '< 1 min read';
        }
        
        return $minutes . ' min read';
    }

    /**
     * Format tags for display
     */
    public static function formatTags(array $tags): array
    {
        return array_map(function($tag) {
            return '#' . Str::slug($tag);
        }, $tags);
    }

    /**
     * Sanitize post title
     */
    public static function sanitizeTitle(string $title): string
    {
        return strip_tags(trim($title));
    }

    /**
     * Validate media file type
     */
    public static function isValidMediaType(string $mimeType): bool
    {
        $validTypes = [
            'image/jpeg',
            'image/png', 
            'image/jpg',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/mov',
            'video/avi'
        ];
        
        return in_array($mimeType, $validTypes);
    }

    /**
     * Get media type (image or video)
     */
    public static function getMediaType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $videoExtensions = ['mp4', 'mov', 'avi'];
        
        return in_array($extension, $videoExtensions) ? 'video' : 'image';
    }

    /**
     * Format view count
     */
    public static function formatViewCount(int $count): string
    {
        if ($count >= 1000000) {
            return number_format($count / 1000000, 1) . 'M';
        } elseif ($count >= 1000) {
            return number_format($count / 1000, 1) . 'K';
        }
        
        return number_format($count);
    }

    /**
     * Get category icon
     */
    public static function getCategoryIcon(string $category): string
    {
        $icons = [
            'Campus Life' => 'bi-building',
            'Academic' => 'bi-book',
            'Announcements' => 'bi-megaphone',
            'Social' => 'bi-people',
            'Career' => 'bi-briefcase',
            'Technology' => 'bi-cpu',
        ];
        
        return $icons[$category] ?? 'bi-chat-dots';
    }

    /**
     * Get category color
     */
    public static function getCategoryColor(string $category): string
    {
        $colors = [
            'Campus Life' => '#667eea',
            'Academic' => '#10b981',
            'Announcements' => '#f59e0b',
            'Social' => '#ec4899',
            'Career' => '#8b5cf6',
            'Technology' => '#3b82f6',
        ];
        
        return $colors[$category] ?? '#6b7280';
    }
}