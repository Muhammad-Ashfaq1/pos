<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (
            ($user->role ?? null) !== 'super_admin'
            && ! (method_exists($user, 'hasRole') && $user->hasRole('super_admin'))
        ) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
