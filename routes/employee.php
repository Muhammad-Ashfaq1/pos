<?php

use App\Http\Controllers\Employee\PanelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved', 'role:employee', 'employee.panel'])
    ->prefix('employee')
    ->name('employee.')
    ->controller(PanelController::class)
    ->group(function () {
        Route::get('/dashboard', 'dashboard')
            ->middleware('permission:dashboard.view')
            ->name('dashboard');
        Route::get('/workspace', 'workspace')
            ->middleware('permission:dashboard.view')
            ->name('workspace');
        Route::get('/pos', 'workspace')
            ->middleware('permission:dashboard.view')
            ->name('pos');
        Route::get('/account', 'account')
            ->middleware('permission:dashboard.view')
            ->name('account');
    });
