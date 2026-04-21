<?php

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::prefix('ecommerce')->name('ecommerce.')->group(function () {
            Route::get('/categories', [CategoryController::class, 'index'])
                ->middleware('permission:category.view')
                ->name('categories.index');
            Route::post('/categories/save', [CategoryController::class, 'save'])
                ->middleware('permission:category.create|category.update')
                ->name('categories.save');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
                ->middleware('permission:category.delete')
                ->name('categories.destroy');
            Route::get('/sub-categories', [App\Http\Controllers\Tenant\EcommerceController::class, 'subCategories'])->name('subcategories.index');
            Route::get('/products', [App\Http\Controllers\Tenant\EcommerceController::class, 'products'])->name('products.index');
        });
    });
