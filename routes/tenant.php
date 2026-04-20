<?php
use App\Http\Controllers\Tenant\Ecommerce\CategoryController;
use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\EcommerceController;
use App\Http\Controllers\Tenant\Ecommerce\SubCategoryController;


Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {

        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard');

        Route::prefix('ecommerce')->name('ecommerce.')->group(function () {

          Route::get('/categories', [EcommerceController::class, 'categories'])
                ->name('categories.index');




             Route::controller(CategoryController::class) ->prefix('categories')->name('categories.')  ->group(function ()
              {

              Route::get('/list', 'list')->name('list');

              Route::post('/save', 'save')->name('save');

              Route::get('/{id}/edit', 'edit')->name('edit');

             Route::delete('/{id}', 'destroy')->name('destroy');
    });


            Route::get('/sub-categories', [EcommerceController::class, 'subCategories'])
                ->name('subcategories.index');

            Route::controller(SubCategoryController::class)->prefix('subcategories') ->name('subcategories.')->group(function () {

            Route::get('/list', 'list')->name('list');

           Route::post('/save', 'save')->name('save');

           Route::get('/{id}/edit', 'edit')->name('edit');

           Route::delete('/{id}', 'destroy')->name('destroy');
    });



            Route::get('/products', [EcommerceController::class, 'products'])
                ->name('products.index');
        });
    });
