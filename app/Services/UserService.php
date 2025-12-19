<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Services;

use App\Models\User;
use App\Contracts\MailServiceInterface;
use App\Contracts\UserCreationStrategyInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private MailServiceInterface $mailService
    ) {}

    /**
     * Create a new user using a creation strategy
     * 
     * Note: Phone and student_id validation/formatting is handled in FormRequest
     * This method receives already validated and formatted data
     * 
     * @param array $data User data
     * @param UserCreationStrategyInterface $strategy Creation strategy to use
     * @return User
     */
    public function createUser(array $data, UserCreationStrategyInterface $strategy): User
    {
        // Use strategy to prepare data (password generation, role setting, etc.)
        $data = $strategy->prepareData($data);

        // Save original password for email (if needed)
        $password = $data['password'] ?? null;

        // Hash password
        $data['password'] = Hash::make($data['password']);

        // Set role from strategy
        $data['role'] = $strategy->getRole();

        // Set email verification status
        if ($strategy->shouldVerifyEmail()) {
            $data['email_verified_at'] = now();
        }

        // Create user
        $user = User::create($data);

        // Send welcome email based on strategy
        if ($strategy->shouldSendEmail() && $password) {
            $emailType = $strategy->getEmailType();
            if ($emailType === 'user') {
                $this->mailService->sendUserWelcomeEmail($user, $password);
            } elseif ($emailType === 'admin') {
                $this->mailService->sendAdminWelcomeEmail($user, $password);
            }
        }

        return $user;
    }

    /**
     * Create a new user (student or club) - Legacy method for backward compatibility
     * 
     * @deprecated Use createUser() with AdminCreatedStudentStrategy instead
     * 
     * @param array $data User data (password will be hashed if provided)
     * @param string $role User role (default: 'student')
     * @param bool $sendWelcomeEmail Whether to send welcome email (default: true for admin-created users)
     * @return User
     */
    public function createUserLegacy(array $data, string $role = 'student', bool $sendWelcomeEmail = true): User
    {
        $strategy = new \App\Services\Strategies\AdminCreatedStudentStrategy($role);
        return $this->createUser($data, $strategy);
    }

    /**
     * Create a new administrator - Legacy method for backward compatibility
     * 
     * @deprecated Use createUser() with AdminCreatedAdminStrategy instead
     * 
     * @param array $data User data
     * @return User
     */
    public function createAdministrator(array $data): User
    {
        $strategy = new \App\Services\Strategies\AdminCreatedAdminStrategy();
        return $this->createUser($data, $strategy);
    }

    /**
     * Update user information
     */
    public function updateUser(User $user, array $data): User
    {
        // Note: Phone validation/formatting is handled in FormRequest
        // This method receives already validated and formatted data

        // Update user
        $user->fill($data);
        $user->save();

        return $user;
    }
}

