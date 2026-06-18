<?php

use App\Http\Controllers\Public\DemoRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::guard('customer')->check()) {
        return redirect()->route('customer.dashboard');
    }

    if (! Auth::check()) {
        return view('public.home');
    }

    $user = Auth::user();

    return redirect()->route($user->defaultDashboardRouteName());
});

Route::post('/demo-request', [DemoRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('demo.request.store');
