<?php

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DropdownController;
use App\Http\Controllers\Tenant\EcommerceController;
use App\Http\Controllers\Tenant\ImageController;
use App\Http\Controllers\Tenant\ProductController;
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
                            ->middleware('permission:category.view|category.create|category.update|subcategory.view|subcategory.create|subcategory.update|product.view|product.create|product.update|products.view|products.manage')
                            ->name('categories');
                        Route::get('/sub-categories', 'subCategories')
                            ->middleware('permission:subcategory.view|subcategory.create|subcategory.update|product.view|product.create|product.update|products.view|products.manage')
                            ->name('subcategories');
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

                Route::prefix('products')
                    ->name('products.')
                    ->controller(ProductController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:product.view|products.view')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:product.view|products.view')
                            ->name('listing');
                        Route::post('/save', 'save')
                            ->middleware('permission:product.create|product.update|products.manage')
                            ->name('save');
                        Route::delete('/{product}', 'destroy')
                            ->middleware('permission:product.delete|products.manage')
                            ->name('destroy');
                    });

                Route::prefix('images')
                    ->name('images.')
                    ->controller(ImageController::class)
                    ->group(function () {
                        Route::post('/upload', 'upload')
                            ->middleware('permission:product.create|product.update|products.manage')
                            ->name('upload');
                        Route::delete('/{image}', 'destroy')
                            ->middleware('permission:product.update|products.manage')
                            ->name('destroy');
                        Route::patch('/{image}/primary', 'setPrimary')
                            ->middleware('permission:product.update|products.manage')
                            ->name('primary');
                    });
            });
    });
