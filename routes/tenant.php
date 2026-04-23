<?php

use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DiscountController;
use App\Http\Controllers\Tenant\DropdownController;
use App\Http\Controllers\Tenant\EcommerceController;
use App\Http\Controllers\Tenant\ImageController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ShopSettingsController;
use App\Http\Controllers\Tenant\ServiceController;
use App\Http\Controllers\Tenant\SubCategoryController;
use App\Http\Controllers\Tenant\VehicleController;
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
                            ->middleware('permission:category.view|category.create|category.update|subcategory.view|subcategory.create|subcategory.update|product.view|product.create|product.update|products.view|products.manage|service.view|service.create|service.update')
                            ->name('categories');
                        Route::get('/sub-categories', 'subCategories')
                            ->middleware('permission:subcategory.view|subcategory.create|subcategory.update|product.view|product.create|product.update|products.view|products.manage')
                            ->name('subcategories');
                        Route::get('/products', 'products')
                            ->middleware('permission:product.view|product.create|product.update|products.view|products.manage|service.view|service.create|service.update')
                            ->name('products');
                        Route::get('/customers', 'customers')
                            ->middleware('permission:customer.view|customer.create|customer.update|vehicle.view|vehicle.create|vehicle.update|pos.bill|customers.view|customers.manage')
                            ->name('customers');
                        Route::get('/vehicles', 'vehicles')
                            ->middleware('permission:vehicle.view|vehicle.create|vehicle.update|customer.view|customer.create|customer.update|pos.bill|vehicles.view|vehicles.manage')
                            ->name('vehicles');
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
                        Route::get('/{product}/edit', 'edit')
                            ->middleware('permission:product.update|products.manage')
                            ->name('edit');
                        Route::post('/save', 'save')
                            ->middleware('permission:product.create|product.update|products.manage')
                            ->name('save');
                        Route::delete('/{product}', 'destroy')
                            ->middleware('permission:product.delete|products.manage')
                            ->name('destroy');
                    });

                Route::prefix('services')
                    ->name('services.')
                    ->controller(ServiceController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:service.view')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:service.view')
                            ->name('listing');
                        Route::get('/{service}/edit', 'edit')
                            ->middleware('permission:service.update')
                            ->name('edit');
                        Route::post('/save', 'save')
                            ->middleware('permission:service.create|service.update')
                            ->name('save');
                        Route::delete('/{service}', 'destroy')
                            ->middleware('permission:service.delete')
                            ->name('destroy');
                    });

                Route::prefix('discounts')
                    ->name('discounts.')
                    ->controller(DiscountController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:discount.manage')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:discount.manage')
                            ->name('listing');
                        Route::get('/{discount}/edit', 'edit')
                            ->middleware('permission:discount.manage')
                            ->name('edit');
                        Route::post('/save', 'save')
                            ->middleware('permission:discount.manage')
                            ->name('save');
                        Route::delete('/{discount}', 'destroy')
                            ->middleware('permission:discount.manage')
                            ->name('destroy');
                    });

                Route::prefix('customers')
                    ->name('customers.')
                    ->controller(CustomerController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:customer.view|customers.view')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:customer.view|customers.view')
                            ->name('listing');
                        Route::get('/{customer}/edit', 'edit')
                            ->middleware('permission:customer.update|customers.manage')
                            ->name('edit');
                        Route::post('/save', 'save')
                            ->middleware('permission:customer.create|customer.update|customers.manage')
                            ->name('save');
                        Route::delete('/{customer}', 'destroy')
                            ->middleware('permission:customer.delete|customers.manage')
                            ->name('destroy');
                    });

                Route::prefix('vehicles')
                    ->name('vehicles.')
                    ->controller(VehicleController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:vehicle.view|vehicles.view')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:vehicle.view|vehicles.view')
                            ->name('listing');
                        Route::get('/{vehicle}/edit', 'edit')
                            ->middleware('permission:vehicle.update|vehicles.manage')
                            ->name('edit');
                        Route::post('/save', 'save')
                            ->middleware('permission:vehicle.create|vehicle.update|vehicles.manage')
                            ->name('save');
                        Route::delete('/{vehicle}', 'destroy')
                            ->middleware('permission:vehicle.delete|vehicles.manage')
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

        Route::prefix('settings')
            ->name('settings.')
            ->controller(ShopSettingsController::class)
            ->group(function () {
                Route::get('/shop-profile', 'edit')
                    ->middleware('permission:settings.manage')
                    ->name('shop-profile.edit');
                Route::put('/shop-profile', 'update')
                    ->middleware('permission:settings.manage')
                    ->name('shop-profile.update');
            });
    });
