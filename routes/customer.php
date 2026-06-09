<?php

use App\Http\Controllers\Customer\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal (web)
|--------------------------------------------------------------------------
|
| Customers sign in through the same /login form as staff (the customer guard
| is tried as a fallback there) and land here. These pages are server-rendered
| and session-authenticated, exactly like the rest of the app. The Flutter app
| uses the token API in routes/api.php instead.
|
*/

Route::prefix('portal')->name('customer.')->controller(PortalController::class)->group(function (): void {
    // Public set-password page (invite / forgot-password links land here).
    Route::get('/reset', 'reset')->name('reset');
    Route::post('/reset', 'resetSubmit')->name('reset.submit');

    // Authenticated portal (session customer guard + tenancy scoping).
    Route::middleware(['auth:customer', 'customer.tenant.init'])->group(function (): void {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/orders', 'orders')->name('orders');
        Route::get('/orders/{order}', 'showOrder')->whereNumber('order')->name('orders.show');
        Route::get('/credits', 'credits')->name('credits');
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/profile', 'updateProfile')->name('profile.update');
    });
});
