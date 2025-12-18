<?php

namespace App\Services\Strategies;

use App\Contracts\UserCreationStrategyInterface;

/**
 * Strategy for student self-registration
 * 
 * - User provides their own password
 * - No welcome email sent (user already knows their password)
 * - Email verification required (user must verify themselves)
 */
class StudentRegistrationStrategy implements UserCreationStrategyInterface
{
    public function prepareData(array $data): array
    {
        // User provides their own password, no need to generate
        // Just ensure password is set (validation should have ensured this)
        return $data;
    }

    public function shouldSendEmail(): bool
    {
        return false; // User already knows their password from registration form
    }

    public function getEmailType(): ?string
    {
        return null; // No email sent
    }

    public function shouldVerifyEmail(): bool
    {
        return false; // User must verify their own email
    }

    public function getRole(): string
    {
        return 'student';
    }
}

