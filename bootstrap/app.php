<?php

use App\Exceptions\InvalidTenantStatusTransitionException;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureCentralUser;
use App\Http\Middleware\EnsureImpersonatingSession;
use App\Http\Middleware\EnsureTenantIsApproved;
use App\Http\Middleware\IsSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/auth.php'));
            Route::middleware('web')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active.user' => EnsureActiveUser::class,
            'central.user' => EnsureCentralUser::class,
            'impersonating' => EnsureImpersonatingSession::class,
            'super_admin' => IsSuperAdmin::class,
            'tenant.approved' => EnsureTenantIsApproved::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidTenantStatusTransitionException $exception, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return back()->with('error', $exception->getMessage());
        });
    })->create();
