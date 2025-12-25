<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Services;

use App\Contracts\MailServiceInterface;
use App\Models\User;
use App\Mail\UserCreatedMail;
use App\Mail\AdminCreatedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService implements MailServiceInterface
{
    /**
     * Send welcome email to user
     */
    public function sendUserWelcomeEmail(User $user, string $password): void
    {
        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $password));
        } catch (\Exception $e) {
            Log::error('Failed to send user welcome email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }
    }

    /**
     * Send welcome email to administrator
     */
    public function sendAdminWelcomeEmail(User $admin, string $password): void
    {
        try {
            Mail::to($admin->email)->send(new AdminCreatedMail($admin, $password));
        } catch (\Exception $e) {
            Log::error('Failed to send admin welcome email: ' . $e->getMessage(), [
                'user_id' => $admin->id,
                'email' => $admin->email,
            ]);
        }
    }
}

