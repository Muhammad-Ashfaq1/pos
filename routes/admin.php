<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemoRequestController;
use App\Http\Controllers\Admin\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified', 'active.user', 'central.user', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/shops', [TenantController::class, 'index'])->name('shops.index');
        Route::get('/shops/{tenant}/edit', [TenantController::class, 'edit'])->name('shops.edit');
        Route::post('/shops/save', [TenantController::class, 'save'])->name('shops.save');

        Route::post('/shops/{tenant}/status/{action}', [TenantController::class, 'changeStatus'])->name('shops.status.change');

        Route::get('/shops/impersonate/{tenant}', [TenantController::class, 'impersonate'])->name('shops.impersonate');

        Route::get('/demo-requests', [DemoRequestController::class, 'index'])->name('demo-requests.index');
        Route::post('/demo-requests/{demoRequest}/status', [DemoRequestController::class, 'updateStatus'])->name('demo-requests.status');
        Route::delete('/demo-requests/{demoRequest}', [DemoRequestController::class, 'destroy'])->name('demo-requests.destroy');
    });

Route::middleware(['web', 'auth', 'impersonating'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/impersonate/stop', [TenantController::class, 'stopImpersonate'])->name('impersonate.stop');
    });
