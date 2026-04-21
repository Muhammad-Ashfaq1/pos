<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'active.user', 'central.user', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/shops', [TenantController::class, 'index'])->name('shops.index');

        Route::post('/shops/{tenant}/status/{action}', [TenantController::class, 'changeStatus'])->name('shops.status.change');

        Route::get('/shops/impersonate/{tenant}', [TenantController::class, 'impersonate'])->name('shops.impersonate');
    });

Route::middleware(['web', 'auth', 'impersonating'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/impersonate/stop', [TenantController::class, 'stopImpersonate'])->name('impersonate.stop');
    });
