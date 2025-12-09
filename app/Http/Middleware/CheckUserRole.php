<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 必须先登录
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to continue.',
                ], 401);
            }

            // 你现在还没有 login 路由，可以先丢回 home 或预留
            return redirect()->route('home')
                             ->with('error', 'Please login to access this page.')
                             ->with('intended', $request->url());
        }

        $user = auth()->user();

        // 只允许 role = user
        if (! $user->hasRole('user')) {   
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only students can access this resource.',
                ], 403);
            }

            abort(403, 'Only students can access this feature.');
        }

        return $next($request);
    }
}
