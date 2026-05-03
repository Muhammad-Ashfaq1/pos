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

                Route::get('/categories', [SharedDataController::class, 'categories'])->name('categories');
                Route::get('/sub-categories', [SharedDataController::class, 'subCategories'])->name('sub-categories');
                Route::get('/products', [SharedDataController::class, 'products'])->name('products');
                Route::get('/search', [SharedDataController::class, 'search'])->name('search');
            });
    });
