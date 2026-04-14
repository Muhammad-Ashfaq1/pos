<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\DashboardController;
 
Route::middleware(['web'])->group(function () {
    Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])->group(function () {
        Route::get('/tenant/dashboard', DashboardController::class)->name('tenant.dashboard');
    });
});
