<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsApproved
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user || empty($user->tenant_id)) {
            return $next($request);
        }

        $tenant = $request->attributes->get('currentTenant') ?? $user->tenant()->first();

        if (! $tenant) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('warning', 'Tenant account could not be found.');
        }

        if (! $tenant->isAccessible()) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('warning', $tenant->status->loginBlockedMessage());
        }

        return $next($request);
    }
}
