<?php

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DropdownController;
use App\Http\Controllers\Tenant\EcommerceController;
use App\Http\Controllers\Tenant\SubCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::prefix('ecommerce')
            ->name('ecommerce.')
            ->group(function () {
                Route::prefix('dropdowns')
                    ->name('dropdowns.')
                    ->controller(DropdownController::class)
                    ->group(function () {
                        Route::get('/categories', 'categories')
                            ->middleware('permission:category.view|category.create|category.update|subcategory.view|subcategory.create|subcategory.update')
                            ->name('categories');
                    });

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

                Route::prefix('sub-categories')
                    ->name('subcategories.')
                    ->controller(SubCategoryController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:subcategory.view')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:subcategory.view')
                            ->name('listing');
                        Route::post('/save', 'save')
                            ->middleware('permission:subcategory.create|subcategory.update')
                            ->name('save');
                        Route::delete('/{subCategory}', 'destroy')
                            ->middleware('permission:subcategory.delete')
                            ->name('destroy');
                    });

                Route::controller(EcommerceController::class)->group(function () {
                    Route::get('/products', 'products')->name('products.index');
                });
            });
    });
