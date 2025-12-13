<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckClubRole {

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        // 开发环境：自动登录 User ID = 1
        if (app()->environment('local') && !Auth::check()) {
            $user = User::find(1);
            if ($user) {
                Auth::login($user);
            }
        }

        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Please login to continue.',
                                ], 401);
            }

            return redirect()->route('login')
                            ->with('error', 'Please login to access this page.')
                            ->with('intended', $request->url());
        }

        $user = auth()->user();

        // Check if user has club role or is admin
        if (!$user->hasRole('club') && !$user->hasRole('admin')) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to access this resource.',
                                ], 403);
            }

            abort(403, 'Only club administrators can access this feature.');
        }

        return $next($request);
    }
}
