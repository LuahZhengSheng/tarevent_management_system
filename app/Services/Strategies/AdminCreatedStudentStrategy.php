<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Services\Strategies;

use App\Contracts\UserCreationStrategyInterface;
use Illuminate\Support\Str;

/**
 * Strategy for admin creating students/clubs
 * 
 * - Password auto-generated if not provided
 * - Welcome email sent with temporary password
 * - Email verification required (user must verify themselves)
 */
class AdminCreatedStudentStrategy implements UserCreationStrategyInterface
{
    private string $role;

    public function __construct(string $role = 'student')
    {
        $this->role = $role;
    }

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
        return 'user'; // Send UserCreatedMail
    }

    public function shouldVerifyEmail(): bool
    {
        return false; // Students/clubs must verify their own email
    }

    public function getRole(): string
    {
        return $this->role; // Can be 'student' or 'club'
    }
}

