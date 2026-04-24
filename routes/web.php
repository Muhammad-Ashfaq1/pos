<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return view('public.home');
    }

    $user = Auth::user();

    return redirect()->route($user->defaultDashboardRouteName());
});
