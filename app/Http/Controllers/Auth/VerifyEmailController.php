<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     * Supports both authenticated and unauthenticated users.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        // Get user by ID from URL parameter
        $user = User::findOrFail($id);

        // Verify the hash matches the user's email
        if (!hash_equals((string) $hash, sha1($user->email))) {
            abort(403, 'Invalid verification link.');
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            // If user is not logged in, redirect to login with success message
            if (!auth()->check() || auth()->id() !== $user->id) {
                return redirect()->route('login')
                    ->with('status', 'Your email has already been verified. Please log in to continue.');
            }
            
            // User is logged in and already verified
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                return redirect()->intended(route('admin.dashboard', absolute: false).'?verified=1');
            }
            if ($user->isClub()) {
                return redirect()->intended(route('club.dashboard', absolute: false).'?verified=1');
            }
            return redirect()->intended(route('home', absolute: false).'?verified=1');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // If user is not logged in, redirect to login with success message
        if (!auth()->check() || auth()->id() !== $user->id) {
            return redirect()->route('login')
                ->with('status', 'Your email has been verified successfully! Please log in to continue.');
        }

        // User is logged in, redirect based on role
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false).'?verified=1');
        }
        if ($user->isClub()) {
            return redirect()->intended(route('club.dashboard', absolute: false).'?verified=1');
        }
        return redirect()->intended(route('home', absolute: false).'?verified=1');
    }
}
