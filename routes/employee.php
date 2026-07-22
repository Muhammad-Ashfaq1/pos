<?php

use App\Http\Controllers\Employee\OrderCartController;
use App\Http\Controllers\Employee\OrderController;
use App\Http\Controllers\Employee\PanelController;
use App\Http\Controllers\Employee\CardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SharedDataController;
use App\Http\Controllers\Tenant\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'employee.panel', 'tenant.init'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', [PanelController::class, 'dashboard'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::get('/dashboard/product-mix', [PanelController::class, 'productMix'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard.product-mix');

        Route::get('/cards', [CardController::class, 'index'])
            ->middleware('permission:cards.view|cards.create|cards.manage')
            ->name('cards.index');
        Route::get('/cards/{type}', [CardController::class, 'typeIndex'])
            ->whereIn('type', ['discount', 'gift', 'reward'])
            ->middleware('permission:cards.view|cards.create|cards.manage')
            ->name('cards.type');
        Route::post('/cards', [CardController::class, 'store'])
            ->middleware('permission:cards.create|cards.manage')
            ->name('cards.store');

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
                Route::get('/cart', [OrderCartController::class, 'show'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->name('cart.show');
                Route::post('/cart', [OrderCartController::class, 'store'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->name('cart.save');
                Route::delete('/cart', [OrderCartController::class, 'destroy'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->name('cart.destroy');

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

                Route::post('/{order}/pay', [OrderController::class, 'pay'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->whereNumber('order')
                    ->name('pay');

                Route::get('/{order}/print', [OrderController::class, 'print'])
                    ->middleware('permission:orders.view')
                    ->whereNumber('order')
                    ->name('print');

                Route::get('/{order}/pdf', [OrderController::class, 'pdf'])
                    ->middleware('permission:orders.view')
                    ->whereNumber('order')
                    ->name('pdf');

                Route::post('/{order}/share', [OrderController::class, 'share'])
                    ->middleware('permission:orders.view')
                    ->whereNumber('order')
                    ->name('share');

                Route::get('/returns', [OrderController::class, 'returns'])
                    ->middleware('permission:orders.view')
                    ->name('returns');

                Route::get('/returns/listing', [OrderController::class, 'returnsListing'])
                    ->middleware('permission:orders.view')
                    ->name('returns.listing');

                Route::get('/returns/history', [OrderController::class, 'returnsHistory'])
                    ->middleware('permission:orders.view')
                    ->name('returns.history');

                Route::post('/{order}/return', [OrderController::class, 'processReturn'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->whereNumber('order')
                    ->name('return');
            });

        // ── Employee Product Management ────────────────────────────────────
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
                Route::get('/{product}/edit', 'edit')
                    ->middleware('permission:product.update|product.create')
                    ->whereNumber('product')
                    ->name('edit');
                Route::post('/save', 'save')
                    ->middleware('permission:product.create|product.update')
                    ->name('save');
                Route::delete('/{product}', 'destroy')
                    ->middleware('permission:product.delete|products.manage')
                    ->whereNumber('product')
                    ->name('destroy');
            });

        // ── Reports (shared with the tenant portal via ReportController) ───
        Route::prefix('reports')
            ->name('reports.')
            ->middleware('permission:reports.view')
            ->controller(ReportController::class)
            ->group(function () {
                Route::get('/{report}/data', 'data')->name('data');
                Route::get('/{report}/export', 'export')->name('export');
                Route::get('/{report}', 'index')->name('index');
            });
    });
