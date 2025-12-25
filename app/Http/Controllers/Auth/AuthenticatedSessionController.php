<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Update last login timestamp
        if (Auth::check()) {
            Auth::user()->updateLastLogin();
        }

        // Generate Bearer Token for API authentication
        $user = Auth::user();
        $token = $user->createToken('web-login')->plainTextToken;
        
        // Store token in session temporarily (will be picked up by frontend JavaScript)
        $request->session()->put('api_token', $token);

        // Redirect based on user role
        // Email verification check is already done in LoginRequest::authenticate()
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        // For club role, redirect to club dashboard
        if ($user->isClub()) {
            return redirect()->intended(route('club.dashboard', absolute: false));
        }

        // For students, redirect to home (events)
        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Revoke all API tokens for this user when logging out
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Flash flag to clear token from localStorage via JavaScript
        $request->session()->flash('clear_token', true);

        return redirect('/');
    }
}

