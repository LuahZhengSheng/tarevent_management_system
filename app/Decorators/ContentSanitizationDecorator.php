<?php

// app/Decorators/ContentSanitizationDecorator.php

namespace App\Decorators;

use Illuminate\Support\Facades\Log;

class ContentSanitizationDecorator extends BasePostDecorator {

    /**
     * Allowed HTML tags for content
     */
    const ALLOWED_TAGS = '<p><br><strong><em><u><a><ul><ol><li><blockquote><h3><h4><h5><h6><code><pre>';

    /**
     * Dangerous attributes to remove
     */
    const DANGEROUS_ATTRIBUTES = [
        'onerror', 'onload', 'onclick', 'onmouseover',
        'onfocus', 'onblur', 'onchange', 'onsubmit',
        'onmouseenter', 'onmouseleave', 'ondblclick',
        'onkeydown', 'onkeyup', 'onkeypress',
    ];

    /**
     * Process and sanitize post data
     *
     * @return array
     */
    public function process(): array {
        $data = parent::process();

        // Sanitize title - remove ALL HTML tags
        if (isset($data['title'])) {
            $data['title'] = $this->sanitizeTitle($data['title']);
        }

        // Sanitize content - remove dangerous HTML but allow safe tags and URLs
        if (isset($data['content'])) {
            $data['content'] = $this->sanitizeContent($data['content']);
        }

        Log::info('Content sanitized', [
            'title_length' => isset($data['title']) ? strlen($data['title']) : 0,
            'content_length' => isset($data['content']) ? strlen($data['content']) : 0,
        ]);

        return $data;
    }

    /**
     * Sanitize title - strip all HTML tags and dangerous characters
     *
     * @param string $title
     * @return string
     */
    protected function sanitizeTitle(string $title): string {
        // Remove all HTML tags
        $title = strip_tags($title);

        // Remove dangerous characters
        $title = preg_replace('/[<>{}]/', '', $title);

        // Remove script content
        $title = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $title);

        // Normalize whitespace
        $title = preg_replace('/\s+/', ' ', $title);

        // Remove null bytes
        $title = str_replace("\0", '', $title);

        return trim($title);
    }

    /**
     * Sanitize content - allow safe HTML and URLs
     *
     * @param string $content
     * @return string
     */
    protected function sanitizeContent(string $content): string {
        // Remove null bytes
        $content = str_replace("\0", '', $content);

        // Remove script tags and their content
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);

        // Remove iframe tags
        $content = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $content);

        // Remove object and embed tags
        $content = preg_replace('/<(object|embed)\b[^>]*>(.*?)<\/\1>/is', '', $content);

        // Strip dangerous tags but keep allowed ones
        $content = strip_tags($content, self::ALLOWED_TAGS);

        // Remove dangerous event handlers (on* attributes)
        foreach (self::DANGEROUS_ATTRIBUTES as $attr) {
            $content = preg_replace('/\s*' . $attr . '\s*=\s*["\'][^"\']*["\']/i', '', $content);
        }

        // Remove javascript: protocol from href and src
        $content = preg_replace('/javascript:/i', '', $content);

        // Remove data: protocol (can be used for XSS)
        $content = preg_replace('/data:text\/html/i', '', $content);

        // Remove vbscript: protocol
        $content = preg_replace('/vbscript:/i', '', $content);

        // Sanitize URLs in href and src attributes
        $content = $this->sanitizeUrls($content);

        // Cleanup extra spaces before closing ">"
        $content = preg_replace('/<(\w+)\s+>/', '<$1>', $content);

        // Remove style attributes (can contain javascript)
        $content = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', '', $content);

        // Remove HTML comments (might hide malicious code)
        $content = preg_replace('/<!--(.*)-->/Uis', '', $content);

        return $content;
    }

    /**
     * Sanitize URLs in HTML attributes
     *
     * @param string $html
     * @return string
     */
    protected function sanitizeUrls(string $html): string {
        // Allow only http, https, and mailto protocols
        $html = preg_replace_callback(
                '/(href|src)\s*=\s*["\']([^"\']+)["\']/i',
                function ($matches) {
                    $attr = $matches[1];
                    $url = $matches[2];

                    // Decode URL entities
                    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');

                    // Check if URL has valid protocol or is relative
                    if (preg_match('/^(https?:\/\/|mailto:|\/)/i', $url)) {
                        // Encode URL for safety
                        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                        return $attr . '="' . $url . '"';
                    }

                    // If no valid protocol, remove the attribute
                    return '';
                },
                $html
        );

        return $html;
    }
}
