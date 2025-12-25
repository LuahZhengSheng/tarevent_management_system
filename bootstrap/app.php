<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\CheckClubRole;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\CheckSuperAdminRole;
use App\Http\Middleware\CheckActiveUser;

return Application::configure(basePath: dirname(__DIR__))
                ->withRouting(
                        web: __DIR__ . '/../routes/web.php',
                        api: __DIR__ . '/../routes/api.php',
                        commands: __DIR__ . '/../routes/console.php',
                        health: '/up',
                )
                ->withMiddleware(function (Middleware $middleware): void {
                    $middleware->alias([
                        'user' => CheckUserRole::class,
                        'club' => CheckClubRole::class,
                        'admin' => CheckAdminRole::class,
                        'super_admin' => CheckSuperAdminRole::class,
                        'check.event.owner' => \App\Http\Middleware\CheckEventOwner::class,
                        'check.active.user' => CheckActiveUser::class,
                    ]);

                    // 在这里添加排除 CSRF 的路由
                    $middleware->validateCsrfTokens(except: [
                        'webhook/stripe', // 这里填你的 URI，不需要写完整域名
                        'webhook/paypal', // 如果有 Paypal webhook 也加在这里
                    ]);
                })
                ->withExceptions(function (Exceptions $exceptions): void {
                    //
                })->create();
