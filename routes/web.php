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
        return redirect()->route('tenant.dashboard');
    }

    abort(403, 'Unable to determine dashboard destination.');
});
