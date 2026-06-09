<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Initializes tenancy from the authenticated portal customer so the
 * BelongsToTenant global scope applies to every query in the request.
 * Mirrors InitializeTenancyFromAuthenticatedUser for the Sanctum-token API.
 */
class InitializeTenancyForCustomer
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user();

        if (! $customer instanceof Customer || empty($customer->tenant_id)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $tenant = $customer->tenant()->first();

        if (! $tenant) {
            return response()->json(['message' => 'Shop account could not be found.'], 404);
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
