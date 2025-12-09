<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // 你目前没有 login 路由，就先回首页
            return route('home');
            // 将来有登录页可以改成：return route('login');
        }

        return null;
    }
}
