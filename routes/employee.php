<?php

use App\Http\Controllers\Employee\PanelController;
use App\Http\Controllers\SharedDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'employee.panel', 'tenant.init'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', [PanelController::class, 'dashboard'])->name('dashboard');

        Route::prefix('order')
            ->name('order.')
            ->group(function () {
                Route::get('/new', [PanelController::class, 'newOrder'])->name('new-order');

                Route::controller(SharedDataController::class)->group(function () {
                    Route::get('/categories', 'categories')->name('categories');
                    Route::get('/sub-categories', 'subCategories')->name('sub-categories');
                    Route::get('/products', 'products')->name('products');
                    Route::get('/search', 'search')->name('search');
                });
            });
    });
