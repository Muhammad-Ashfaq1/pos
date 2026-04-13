<?php

use App\Http\Controllers\Admin\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/shops', [TenantController::class, 'index'])->name('shops.index');
    Route::get('/shops/pending', [TenantController::class, 'pending'])->name('shops.pending');

    Route::post('/shops/{id}/status/{action}', [TenantController::class, 'changeStatus'])->name('shops.status.change');

    Route::get('/shops/impersonate/{id}', [TenantController::class, 'impersonate'])->name('shops.impersonate');
    Route::get('/impersonate/stop', [TenantController::class, 'stopImpersonate'])->name('impersonate.stop');
});
