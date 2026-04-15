<?php

use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::prefix('ecommerce')->name('ecommerce.')->group(function () {
            Route::get('/categories', [App\Http\Controllers\Tenant\EcommerceController::class, 'categories'])->name('categories.index');
            Route::get('/sub-categories', [App\Http\Controllers\Tenant\EcommerceController::class, 'subCategories'])->name('subcategories.index');
            Route::get('/products', [App\Http\Controllers\Tenant\EcommerceController::class, 'products'])->name('products.index');
        });
    });
