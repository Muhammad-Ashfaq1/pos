<?php

use App\Http\Controllers\Employee\PanelController;
use App\Http\Controllers\Employee\OrderController;
use App\Http\Controllers\SharedDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'employee.panel', 'tenant.init'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', [PanelController::class, 'dashboard'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::prefix('order')
            ->name('order.')
            ->group(function () {
                Route::get('/', [OrderController::class, 'index'])
                    ->middleware('permission:orders.view')
                    ->name('index');
                Route::get('/listing', [OrderController::class, 'listing'])
                    ->middleware('permission:orders.view')
                    ->name('listing');
                Route::get('/new', [OrderController::class, 'create'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->name('new-order');
                Route::post('/save', [OrderController::class, 'store'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->name('save');

                Route::controller(SharedDataController::class)->group(function () {
                    Route::get('/categories', 'categories')
                        ->middleware('permission:orders.create|pos.bill')
                        ->name('categories');
                    Route::get('/sub-categories', 'subCategories')
                        ->middleware('permission:orders.create|pos.bill')
                        ->name('sub-categories');
                    Route::get('/products', 'products')
                        ->middleware('permission:orders.create|pos.bill')
                        ->name('products');
                    Route::get('/search', 'search')
                        ->middleware('permission:orders.create|pos.bill')
                        ->name('search');
                });

                Route::get('/{order}', [OrderController::class, 'show'])
                    ->middleware('permission:orders.view')
                    ->whereNumber('order')
                    ->name('show');
            });
    });
