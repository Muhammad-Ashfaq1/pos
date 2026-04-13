<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if ($user->isSuperAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->tenant_id) {
        $domain = $user->tenant?->domains()->value('domain');

        if ($domain) {
            return redirect()->away(request()->getScheme().'://'.$domain.'/dashboard');
        }

        return redirect()->route('login')->with('warning', 'Tenant domain is not configured yet.');
    }

    abort(403, 'Unable to determine dashboard destination.');
});
