<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
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

        $tenant = Tenant::query()->find($user->tenant_id);

        if (! $tenant || $tenant->status !== 'approved') {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('warning', 'Your shop account is awaiting approval.');
        }

        return $next($request);
    }
}
