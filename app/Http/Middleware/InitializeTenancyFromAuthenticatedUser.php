<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyFromAuthenticatedUser
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user || empty($user->tenant_id)) {
            return $next($request);
        }

        $tenant = $this->tenantContext->fromUser($user);

        if (! $tenant) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('warning', 'Tenant account could not be found.');
        }

        $request->attributes->set('currentTenant', $tenant);
        $this->tenantContext->initialize($tenant);

        try {
            return $next($request);
        } finally {
            $this->tenantContext->end();
        }
    }
}
