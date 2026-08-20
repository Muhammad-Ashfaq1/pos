<?php

use App\Http\Controllers\Customer\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal (web)
|--------------------------------------------------------------------------
|
| Pages are thin Blade shells. Customer data is loaded via axios from
| /api/v1/customer/* — the same JSON API the Flutter app will use.
| Session auth (login / staff impersonate) gets a Sanctum Bearer token
| from GET portal/api-token.
|
*/

Route::prefix('portal')->name('customer.')->controller(PortalController::class)->group(function (): void {
    // Public set-password page (invite / forgot-password links land here).
    Route::get('/reset', 'reset')->name('reset');
    Route::post('/reset', 'resetSubmit')->name('reset.submit');

    // Authenticated portal (session customer guard + tenancy scoping).
    Route::middleware(['auth:customer', 'customer.tenant.init'])->group(function (): void {
        Route::get('/api-token', 'apiToken')->name('api-token');
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/orders', 'orders')->name('orders');
        Route::get('/orders/{order}', 'showOrder')->whereNumber('order')->name('orders.show');
        Route::get('/orders/{order}/pdf', 'downloadOrderPdf')->whereNumber('order')->name('orders.pdf');
        Route::get('/credits', 'credits')->name('credits');
        Route::get('/vehicles', 'vehicles')->name('vehicles');
        Route::redirect('/profile', '/account/profile')->name('profile');
        Route::redirect('/password', '/account/password')->name('password');
    });
});
