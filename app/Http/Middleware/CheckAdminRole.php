<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
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

        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
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