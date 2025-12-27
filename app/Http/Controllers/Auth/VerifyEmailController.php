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
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
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
            // If user is logged in, redirect based on role
            if (auth()->check() && auth()->id() === $user->id) {
                if ($user->isAdmin() || $user->isSuperAdmin()) {
                    return redirect()->route('admin.dashboard')
                        ->with('status', 'Your email is already verified.');
                }
                if ($user->isClub()) {
                    return redirect()->route('club.dashboard')
                        ->with('status', 'Your email is already verified.');
                }
                return redirect()->route('home')
                    ->with('status', 'Your email is already verified.');
            }
            
            // If not logged in, auto-login and redirect
            Auth::login($user);
            $request->session()->regenerate();
            
            // Generate Bearer Token
            $token = $user->createToken('web-login')->plainTextToken;
            $request->session()->put('api_token', $token);
            
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('status', 'Your email is already verified. Welcome back!');
            }
            if ($user->isClub()) {
                return redirect()->route('club.dashboard')
                    ->with('status', 'Your email is already verified. Welcome back!');
            }
            return redirect()->route('home')
                ->with('status', 'Your email is already verified. Welcome back!');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // If user is logged in, redirect based on role
        if (auth()->check() && auth()->id() === $user->id) {
            // Generate Bearer Token if not already in session
            if (!$request->session()->has('api_token')) {
                $token = $user->createToken('web-login')->plainTextToken;
                $request->session()->put('api_token', $token);
            }
            
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('status', 'Your email has been verified successfully!');
            }
            if ($user->isClub()) {
                return redirect()->route('club.dashboard')
                    ->with('status', 'Your email has been verified successfully!');
            }
            return redirect()->route('home')
                ->with('status', 'Your email has been verified successfully!');
        }

        // If not logged in, auto-login the user
        Auth::login($user);
        $request->session()->regenerate();
        
        // Generate Bearer Token
        $token = $user->createToken('web-login')->plainTextToken;
        $request->session()->put('api_token', $token);
        
        // Redirect based on role
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('status', 'Email verified successfully! You have been automatically logged in.');
        }
        if ($user->isClub()) {
            return redirect()->route('club.dashboard')
                ->with('status', 'Email verified successfully! You have been automatically logged in.');
        }
        return redirect()->route('home')
            ->with('status', 'Email verified successfully! You have been automatically logged in.');
    }
}
