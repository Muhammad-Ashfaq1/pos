<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Initializes tenancy when account settings are accessed by a portal customer
 * or a tenant-scoped staff user.
 */
class InitializeAccountContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('customer')->check()) {
            return app(InitializeTenancyForCustomer::class)->handle($request, $next);
        }

        $user = auth()->user();

        if ($user && ! empty($user->tenant_id)) {
            return app(InitializeTenancyFromAuthenticatedUser::class)->handle($request, $next);
        }

        return $next($request);
    }
}
