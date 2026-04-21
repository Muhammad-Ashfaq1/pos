<?php

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\EcommerceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::prefix('ecommerce')
            ->name('ecommerce.')
            ->group(function () {
                Route::prefix('categories')
                    ->name('categories.')
                    ->controller(CategoryController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:category.view')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:category.view')
                            ->name('listing');
                        Route::post('/save', 'save')
                            ->middleware('permission:category.create|category.update')
                            ->name('save');
                        Route::delete('/{category}', 'destroy')
                            ->middleware('permission:category.delete')
                            ->name('destroy');
                    });

                Route::controller(EcommerceController::class)->group(function () {
                    Route::get('/sub-categories', 'subCategories')->name('subcategories.index');
                    Route::get('/products', 'products')->name('products.index');
                });
            });
    });
