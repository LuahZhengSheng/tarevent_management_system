<?php
// app/Decorators/ValidationPostDecorator.php

namespace App\Decorators;

use Illuminate\Support\Facades\Log;

class ValidationPostDecorator extends BasePostDecorator
{
    const MIN_TITLE_LENGTH = 5;
    const MAX_TITLE_LENGTH = 100;

    const MIN_CONTENT_LENGTH = 1;
    const MAX_CONTENT_LENGTH = 500000;

    public function process(): array
    {
        $data = parent::process();

        // status：从 decorator data 或 request 拿
        $status = $data['status'] ?? $this->request->input('status', 'published');

        // Validate title length after sanitization (draft/published 都要)
        if (isset($data['title'])) {
            $this->validateTitle($data['title']);
        }

        // draft：允许 content / category / visibility / clubs 不完整
        if ($status === 'draft') {
            Log::info('Draft validation: skipping content/club validations', [
                'title_length' => isset($data['title']) ? strlen($data['title']) : 0,
            ]);

            return $data;
        }

        // published：维持你原本严格验证
        if (isset($data['content'])) {
            $this->validateContent($data['content']);
        }

        if (isset($data['visibility']) && $data['visibility'] === 'club_only') {
            $this->validateClubVisibility($data);
        }

        Log::info('Post data validated successfully', [
            'title_length' => isset($data['title']) ? strlen($data['title']) : 0,
            'content_length' => isset($data['content']) ? strlen(strip_tags($data['content'])) : 0,
        ]);

        return $data;
    }

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

        if (trim($title) === '') {
            throw new \Exception('Title cannot be empty or contain only whitespace.');
        }
    }

    protected function validateContent(string $content): void
    {
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

        if (trim($textContent) === '') {
            throw new \Exception('Content cannot be empty or contain only whitespace.');
        }
    }

    protected function validateClubVisibility(array $data): void
    {
        if (empty($data['club_id']) && !$this->request->filled('club_ids')) {
            throw new \Exception('Club selection is required for club-only posts.');
        }

        if ($this->request->filled('club_ids')) {
            $clubIds = $this->request->input('club_ids');

            if (!is_array($clubIds) || empty($clubIds)) {
                throw new \Exception('At least one club must be selected for club-only posts.');
            }
        }
    }
}
