<?php

use App\Http\Controllers\DiscountGroupController;
use App\Http\Controllers\Employee\InvoiceController;
use App\Http\Controllers\Employee\OrderCartController;
use App\Http\Controllers\Employee\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SharedDataController;
use App\Http\Controllers\Tenant\CardController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DiscountController;
use App\Http\Controllers\Tenant\DropdownController;
use App\Http\Controllers\Tenant\ImageController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ProductTypeController;
use App\Http\Controllers\Tenant\RolesPermissionsController;
use App\Http\Controllers\Tenant\ServiceController;
use App\Http\Controllers\Tenant\ShopSettingsController;
use App\Http\Controllers\Tenant\SubCategoryController;
use App\Http\Controllers\Tenant\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'active.user', 'tenant.init', 'tenant.approved'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // ── Reports (shared with the employee panel via ReportController) ──
        Route::prefix('reports')
            ->name('reports.')
            ->middleware('permission:reports.view')
            ->controller(ReportController::class)
            ->group(function () {
                Route::get('/{report}/data', 'data')->name('data');
                Route::get('/{report}/export', 'export')->name('export');
                Route::get('/{report}', 'index')->name('index');
            });

        // ── Orders / POS / Invoices (shared Employee controllers + OrderSurface) ──
        Route::prefix('invoices')
            ->name('invoices.')
            ->group(function () {
                Route::get('/', [InvoiceController::class, 'index'])
                    ->middleware('permission:orders.view')
                    ->name('index');
                Route::get('/listing', [InvoiceController::class, 'listing'])
                    ->middleware('permission:orders.view')
                    ->name('listing');
                Route::get('/create', [InvoiceController::class, 'create'])
                    ->middleware('permission:orders.create|pos.bill')
                    ->name('create');
            });

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

                Route::get('/returns', [OrderController::class, 'returns'])
                    ->middleware('permission:returns.view|orders.view')
                    ->name('returns');
                Route::get('/returns/listing', [OrderController::class, 'returnsListing'])
                    ->middleware('permission:returns.view|orders.view')
                    ->name('returns.listing');
                Route::get('/returns/history', [OrderController::class, 'returnsHistory'])
                    ->middleware('permission:returns.view|orders.view')
                    ->name('returns.history');

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
                Route::post('/{order}/return', [OrderController::class, 'processReturn'])
                    ->middleware('permission:refunds.manage|orders.create|pos.bill')
                    ->whereNumber('order')
                    ->name('return');
            });

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
                        Route::get('/services', 'services')
                            ->middleware('permission:service.view|service.create|service.update|orders.create|pos.bill')
                            ->name('services');
                        Route::get('/discounts', 'discounts')
                            ->middleware('permission:discount.manage|discount.apply_item|product.create|product.update|products.manage')
                            ->name('discounts');
                        Route::get('/customers', 'customers')
                            ->middleware('permission:customer.view|customer.create|customer.update|vehicle.view|vehicle.create|vehicle.update|pos.bill|customers.view|customers.manage')
                            ->name('customers');
                        Route::get('/vehicles', 'vehicles')
                            ->middleware('permission:vehicle.view|vehicle.create|vehicle.update|customer.view|customer.create|customer.update|pos.bill|vehicles.view|vehicles.manage')
                            ->name('vehicles');
                        Route::get('/discount-groups', 'discountGroups')
                            ->middleware('permission:discount-group.view|discount-group.manage|customer.create|customer.update|customers.manage')
                            ->name('discount-groups');
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

                Route::prefix('product-types')
                    ->name('product-types.')
                    ->controller(ProductTypeController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:product-type.view')
                            ->name('index');
                        Route::get('/listing', 'listing')
                            ->middleware('permission:product-type.view')
                            ->name('listing');
                        Route::post('/save', 'save')
                            ->middleware('permission:product-type.create|product-type.update')
                            ->name('save');
                        Route::delete('/{productType}', 'destroy')
                            ->middleware('permission:product-type.delete')
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

                Route::prefix('cards')
                    ->name('cards.')
                    ->controller(CardController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->middleware('permission:cards.view|cards.manage')
                            ->name('index');

                        Route::prefix('{type}')
                            ->whereIn('type', ['discount', 'gift', 'reward'])
                            ->group(function () {
                                Route::get('/', 'typeIndex')
                                    ->middleware('permission:cards.view|cards.manage')
                                    ->name('type');
                                Route::get('/listing', 'listing')
                                    ->middleware('permission:cards.view|cards.manage')
                                    ->name('listing');
                                Route::get('/{card}/edit', 'edit')
                                    ->middleware('permission:cards.update|cards.manage')
                                    ->name('edit');
                                Route::post('/save', 'save')
                                    ->middleware('permission:cards.create|cards.update|cards.manage')
                                    ->name('save');
                                Route::delete('/{card}', 'destroy')
                                    ->middleware('permission:cards.delete|cards.manage')
                                    ->name('destroy');
                            });
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
                        Route::get('/{customer}/credit-history', 'creditHistory')
                            ->middleware('permission:customer.view|customers.view')
                            ->name('credit-history');
                        Route::post('/{customer}/invite-portal', 'invitePortal')
                            ->middleware('permission:customer.update|customers.manage')
                            ->name('invite-portal');
                        Route::post('/{customer}/adjust-credit', 'adjustCredit')
                            ->middleware('permission:customer.update|customers.manage')
                            ->name('adjust-credit');
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

                Route::prefix('shop-profile')
                    ->name('shop-profile.')
                    ->middleware('permission:settings.manage')
                    ->group(function () {
                        Route::get('/general', 'general')->name('general');
                        Route::post('/general/save', 'saveGeneral')->name('general.save');

                        Route::get('/branding', 'branding')->name('branding');
                        Route::post('/branding/save', 'saveBranding')->name('branding.save');

                        Route::get('/regional', 'regional')->name('regional');
                        Route::post('/regional/save', 'saveRegional')->name('regional.save');

                        Route::get('/operations', 'operations')->name('operations');
                        Route::post('/operations/save', 'saveOperations')->name('operations.save');

                        Route::get('/notifications', 'notifications')->name('notifications');
                        Route::post('/notifications/save', 'saveNotifications')->name('notifications.save');

                        Route::get('/order-invoice', 'orderInvoice')->name('order-invoice');
                        Route::post('/order-invoice/save', 'saveOrderInvoice')->name('order-invoice.save');
                    });

                Route::prefix('roles-permissions')
                    ->name('roles-permissions.')
                    ->controller(RolesPermissionsController::class)
                    ->middleware('permission:roles.manage|settings.manage')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/role-permissions', 'rolePermissions')->name('role-permissions');
                        Route::post('/roles/save', 'saveRole')->name('roles.save');
                        Route::delete('/roles/{role}', 'deleteRole')->name('roles.destroy');
                        Route::post('/permissions/sync', 'syncPermissions')->name('permissions.sync');
                        Route::get('/staff', 'staffListing')->name('staff.listing');
                        Route::get('/staff/{user}/impersonate', 'impersonateStaff')->name('staff.impersonate');
                    });
            });

        Route::prefix('discounts')->name('discounts.')->group(function () {
            Route::prefix('group')->name('group.')->controller(DiscountGroupController::class)->group(function () {
                Route::get('/', 'index')
                    ->middleware('permission:discount-group.view|discount-group.manage')
                    ->name('index');
                Route::post('/store', 'store')
                    ->middleware('permission:discount-group.manage')
                    ->name('store');
                Route::put('/{discountGroup}', 'update')
                    ->middleware('permission:discount-group.manage')
                    ->name('update');
                Route::delete('/{discountGroup}', 'destroy')
                    ->middleware('permission:discount-group.manage')
                    ->name('delete');
            });
        });
    });
