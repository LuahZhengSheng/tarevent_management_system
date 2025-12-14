<?php
// app/Decorators/TagsPostDecorator.php

namespace App\Decorators;

use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TagsPostDecorator extends BasePostDecorator
{
    /**
     * Maximum number of tags allowed
     */
    const MAX_TAGS = 10;

    /**
     * Maximum tag name length
     */
    const MAX_TAG_LENGTH = 50;

    /**
     * Process tags - Only allow existing tags (created through request endpoint)
     *
     * @return array
     */
    public function process(): array
    {
        $data = parent::process();
        
        // Process tags if present
        if ($this->request->filled('tags')) {
            try {
                $tagNames = $this->request->input('tags');
                
                // Validate tags array
                if (!is_array($tagNames)) {
                    throw new \Exception('Tags must be an array.');
                }
                
                // Validate count
                if (count($tagNames) > self::MAX_TAGS) {
                    throw new \Exception('Maximum ' . self::MAX_TAGS . ' tags allowed.');
                }
                
                $processedTags = [];
                $notFoundTags = [];
                
                foreach ($tagNames as $tagName) {
                    $tagName = $this->sanitizeTagName($tagName);
                    
                    // Skip empty or too long tags
                    if (empty($tagName) || strlen($tagName) > self::MAX_TAG_LENGTH) {
                        continue;
                    }
                    
                    // IMPORTANT: Only use existing tags (active or pending)
                    // Users must create tags through the request endpoint first
                    $tag = Tag::where('name', $tagName)
                        ->whereIn('status', ['active', 'pending'])
                        ->first();
                    
                    if ($tag) {
                        $processedTags[] = $tag->id;
                    } else {
                        $notFoundTags[] = $tagName;
                    }
                }
                
                // If any tags not found, log warning
                if (!empty($notFoundTags)) {
                    Log::warning('Tags not found or not approved', [
                        'tags' => $notFoundTags,
                        'user_id' => auth()->id(),
                    ]);
                }
                
                $data['tag_ids'] = array_unique($processedTags);
                
                Log::info('Tags processed', [
                    'total_tags' => count($processedTags),
                    'not_found_tags' => count($notFoundTags),
                ]);
                
            } catch (\Exception $e) {
                Log::error('Tag processing failed', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
        
        return $data;
    }
    
    /**
     * Sanitize tag name
     *
     * @param string $tagName
     * @return string
     */
    protected function sanitizeTagName(string $tagName): string
    {
        // Convert to lowercase
        $tagName = strtolower($tagName);
        
        // Trim whitespace
        $tagName = trim($tagName);
        
        // Remove HTML tags
        $tagName = strip_tags($tagName);
        
        // Remove special characters except alphanumeric, spaces, hyphens, and underscores
        $tagName = preg_replace('/[^a-z0-9\s\-_]/u', '', $tagName);
        
        // Normalize multiple spaces to single space
        $tagName = preg_replace('/\s+/', ' ', $tagName);
        
        return $tagName;
    }
}
