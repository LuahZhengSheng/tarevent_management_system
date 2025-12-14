<?php

if (!function_exists('sanitize_html')) {

    /**
     * Sanitize HTML content
     */
    function sanitize_html($html, $allowedTags = null) {
        if ($allowedTags === null) {
            $allowedTags = '<p><br><strong><em><u><a><ul><ol><li><blockquote><h3><h4><h5><h6><code><pre>';
        }

        // Strip dangerous tags
        $html = strip_tags($html, $allowedTags);

        // Remove dangerous attributes
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);

        return $html;
    }

}

if (!function_exists('sanitize_title')) {

    /**
     * Sanitize post title
     */
    function sanitize_title($title) {
        // Remove all HTML tags
        $title = strip_tags($title);

        // Remove dangerous characters
        $title = preg_replace('/[<>{}]/', '', $title);

        // Normalize whitespace
        $title = preg_replace('/\s+/', ' ', $title);

        return trim($title);
    }

}

if (!function_exists('format_file_size')) {

    /**
     * Format bytes to human readable size
     */
    function format_file_size($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

}

if (!function_exists('extract_urls')) {

    /**
     * Extract URLs from text
     */
    function extract_urls($text) {
        $pattern = '/\b(?:https?:\/\/|www\.)\S+/i';
        preg_match_all($pattern, $text, $matches);
        return $matches[0] ?? [];
    }

}

if (!function_exists('make_links_clickable')) {

    /**
     * Convert URLs in text to clickable links
     */
    function make_links_clickable($text) {
        $pattern = '/\b(https?:\/\/[^\s<]+)/i';
        $replacement = '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>';
        return preg_replace($pattern, $replacement, $text);
    }

}


if (!function_exists('post_excerpt')) {

    function post_excerpt(string $content, int $length = 150): string {
        return App\Helpers\PostHelper::generateExcerpt($content, $length);
    }

}

if (!function_exists('post_read_time')) {

    function post_read_time(string $content): string {
        return App\Helpers\PostHelper::calculateReadTime($content);
    }

}

if (!function_exists('format_view_count')) {

    function format_view_count(int $count): string {
        return App\Helpers\PostHelper::formatViewCount($count);
    }

}

if (!function_exists('category_icon')) {

    function category_icon(string $category): string {
        return App\Helpers\PostHelper::getCategoryIcon($category);
    }

}

if (!function_exists('category_color')) {

    function category_color(string $category): string {
        return App\Helpers\PostHelper::getCategoryColor($category);
    }

}

if (!function_exists('render_form_loading_overlay')) {

    function render_form_loading_overlay(): string {
        return <<<'HTML'
<div id="formLoadingOverlay" class="loading-overlay" style="display:none;">
    <div class="loading-backdrop"></div>
    <div class="loading-content">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="loading-text">Processing your post...</div>
    </div>
</div>
HTML;
    }

}
