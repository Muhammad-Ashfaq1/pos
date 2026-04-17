<?php
use App\Http\Controllers\Tenant\Ecommerce\CategoryController;
use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\EcommerceController;


Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {

        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard');

        Route::prefix('ecommerce')->name('ecommerce.')->group(function () {


            Route::get('/categories', [EcommerceController::class, 'categories'])
                ->name('categories.index');

          Route::controller(CategoryController::class)->group(function () {

    Route::post('/categories/save', 'save')->name('categories.save');

    Route::get('/categories/{id}/edit', 'edit')->name('categories.edit');

    Route::delete('/categories/{id}', 'destroy')->name('categories.destroy');



            });


            Route::get('/sub-categories', [EcommerceController::class, 'subCategories'])
                ->name('subcategories.index');

            Route::get('/products', [EcommerceController::class, 'products'])
                ->name('products.index');
        });
    });
