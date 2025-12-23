<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Contracts;

/**
 * Strategy interface for user creation
 * 
 * Different strategies handle different user creation scenarios:
 * - Student self-registration
 * - Admin creating students/clubs
 * - Admin creating administrators
 */
interface UserCreationStrategyInterface
{
    /**
     * Prepare user data before creation
     * This includes password generation, role setting, etc.
     * 
     * @param array $data Raw user data
     * @return array Prepared user data
     */
    public function prepareData(array $data): array;

    /**
     * Determine if welcome email should be sent
     * 
     * @return bool
     */
    public function shouldSendEmail(): bool;

    /**
     * Get the email type to send
     * Returns 'user' for UserCreatedMail, 'admin' for AdminCreatedMail, or null
     * 
     * @return string|null
     */
    public function getEmailType(): ?string;

    /**
     * Determine if email should be auto-verified
     * 
     * @return bool
     */
    public function shouldVerifyEmail(): bool;

    /**
     * Get the role for the user
     * 
     * @return string
     */
    public function getRole(): string;
}

