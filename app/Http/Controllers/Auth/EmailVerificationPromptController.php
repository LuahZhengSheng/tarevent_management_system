<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            // Redirect based on user role
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }
            if ($user->isClub()) {
                return redirect()->intended(route('club.dashboard', absolute: false));
            }
            return redirect()->intended(route('home', absolute: false));
        }
        
        return view('auth.verify-email');
    }
}
