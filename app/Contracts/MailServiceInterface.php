<?php

namespace App\Contracts;

use App\Models\User;

interface MailServiceInterface
{
    /**
     * Send welcome email to user
     */
    public function sendUserWelcomeEmail(User $user, string $password): void;

    /**
     * Send welcome email to administrator
     */
    public function sendAdminWelcomeEmail(User $admin, string $password): void;
}

