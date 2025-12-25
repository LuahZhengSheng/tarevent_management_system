<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Services\Strategies;

use App\Contracts\UserCreationStrategyInterface;
use Illuminate\Support\Str;

/**
 * Strategy for admin creating administrators
 * 
 * - Password auto-generated if not provided
 * - Welcome email sent with temporary password
 * - Email auto-verified (admins don't need to verify)
 */
class AdminCreatedAdminStrategy implements UserCreationStrategyInterface
{
    public function prepareData(array $data): array
    {
        // Auto-generate password if not provided
        if (!isset($data['password']) || empty($data['password'])) {
            $data['password'] = Str::random(12);
        }

        return $data;
    }

    public function shouldSendEmail(): bool
    {
        return true; // Send welcome email with temporary password
    }

    public function getEmailType(): ?string
    {
        return 'admin'; // Send AdminCreatedMail
    }

    public function shouldVerifyEmail(): bool
    {
        return true; // Admins are auto-verified
    }

    public function getRole(): string
    {
        return 'admin';
    }
}

