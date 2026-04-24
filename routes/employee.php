<?php

use App\Http\Controllers\Employee\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)
            ->middleware('permission:dashboard.view')
            ->name('dashboard');
    });
