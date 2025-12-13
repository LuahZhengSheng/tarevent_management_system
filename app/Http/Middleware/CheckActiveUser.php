<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser {

    /**
     * Handle an incoming request.
     * Ensures the user's account is active (not suspended)
     */
    public function handle(Request $request, Closure $next): Response {
        // 开发环境：自动登录 User ID = 1
        if (app()->environment('local') && !Auth::check()) {
            $user = User::find(1);
            if ($user) {
                Auth::login($user);
            }
        }

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user account is suspended
        if ($user->isSuspended()) {
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Your account has been suspended. Please contact support.',
                                ], 403);
            }

            return redirect()->route('login')
                            ->withErrors(['email' => 'Your account has been suspended. Please contact support for assistance.']);
        }

        // Check if user account is inactive
        if (!$user->isActive()) {
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Your account is inactive.',
                                ], 403);
            }

            return redirect()->route('login')
                            ->withErrors(['email' => 'Your account is inactive. Please verify your email or contact support.']);
        }

        return $next($request);
    }
}
