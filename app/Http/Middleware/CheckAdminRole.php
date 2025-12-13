<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole {

    /**
     * Handle an incoming request.
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
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Authentication required.',
                                ], 401);
            }

            return redirect()->route('login')
                            ->with('error', 'Please login to continue.')
                            ->with('intended', $request->url());
        }

        if (!auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Administrator access required.',
                                ], 403);
            }

            abort(403, 'This area is restricted to administrators only.');
        }

        return $next($request);
    }
}
