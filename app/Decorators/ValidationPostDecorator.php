<?php
// app/Decorators/ValidationPostDecorator.php

namespace App\Decorators;

use Illuminate\Support\Facades\Log;

class ValidationPostDecorator extends BasePostDecorator
{
    /**
     * Minimum title length
     */
    const MIN_TITLE_LENGTH = 5;

    /**
     * Maximum title length
     */
    const MAX_TITLE_LENGTH = 100;

    /**
     * Minimum content length (after sanitization)
     */
    const MIN_CONTENT_LENGTH = 1;

    /**
     * Maximum content length (after sanitization)
     */
    const MAX_CONTENT_LENGTH = 500000;

    /**
     * Process and validate data
     *
     * @return array
     * @throws \Exception
     */
    public function process(): array
    {
        $data = parent::process();
        
        // Validate title length after sanitization
        if (isset($data['title'])) {
            $this->validateTitle($data['title']);
        }
        
        // Validate content length after sanitization
        if (isset($data['content'])) {
            $this->validateContent($data['content']);
        }
        
        // Validate visibility and club_id relationship
        if (isset($data['visibility']) && $data['visibility'] === 'club_only') {
            $this->validateClubVisibility($data);
        }
        
        Log::info('Post data validated successfully', [
            'title_length' => isset($data['title']) ? strlen($data['title']) : 0,
            'content_length' => isset($data['content']) ? strlen(strip_tags($data['content'])) : 0,
        ]);
        
        return $data;
    }
    
    /**
     * Validate title
     *
     * @param string $title
     * @throws \Exception
     */
    protected function validateTitle(string $title): void
    {
        $length = strlen($title);
        
        if ($length < self::MIN_TITLE_LENGTH) {
            throw new \Exception(
                "Title is too short after sanitization. Minimum " . self::MIN_TITLE_LENGTH . " characters required, got {$length}."
            );
        }
        
        if ($length > self::MAX_TITLE_LENGTH) {
            throw new \Exception(
                "Title is too long after sanitization. Maximum " . self::MAX_TITLE_LENGTH . " characters allowed, got {$length}."
            );
        }
        
        // Check if title is just whitespace
        if (trim($title) === '') {
            throw new \Exception('Title cannot be empty or contain only whitespace.');
        }
    }
    
    /**
     * Validate content
     *
     * @param string $content
     * @throws \Exception
     */
    protected function validateContent(string $content): void
    {
        // Get text length without HTML tags
        $textContent = strip_tags($content);
        $length = strlen($textContent);
        
        if ($length < self::MIN_CONTENT_LENGTH) {
            throw new \Exception(
                "Content is too short after sanitization. Minimum " . self::MIN_CONTENT_LENGTH . " character required."
            );
        }
        
        if ($length > self::MAX_CONTENT_LENGTH) {
            throw new \Exception(
                "Content is too long after sanitization. Maximum " . self::MAX_CONTENT_LENGTH . " characters allowed, got {$length}."
            );
        }
        
        // Check if content is just whitespace
        if (trim($textContent) === '') {
            throw new \Exception('Content cannot be empty or contain only whitespace.');
        }
    }
    
    /**
     * Validate club visibility
     *
     * @param array $data
     * @throws \Exception
     */
    protected function validateClubVisibility(array $data): void
    {
        // When visibility is club_only, club_id must be present
        if (empty($data['club_id']) && !$this->request->filled('club_ids')) {
            throw new \Exception('Club selection is required for club-only posts.');
        }
        
        // If club_ids is provided (from form), validate it
        if ($this->request->filled('club_ids')) {
            $clubIds = $this->request->input('club_ids');
            
            if (!is_array($clubIds) || empty($clubIds)) {
                throw new \Exception('At least one club must be selected for club-only posts.');
            }
        }
    }
}
