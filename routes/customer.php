<?php

use App\Http\Controllers\Customer\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal (web shell)
|--------------------------------------------------------------------------
|
| Thin Blade pages that call the /api/v1/customer/* endpoints via axios with a
| Sanctum Bearer token. The same API backs the future Flutter app. These pages
| hold no server-side auth — the client redirects based on the stored token.
|
*/

Route::prefix('portal')->name('customer.')->controller(PortalController::class)->group(function (): void {
    Route::get('/login', 'login')->name('login');
    Route::get('/reset', 'reset')->name('reset');

    // Deep-linkable app sections all render the same shell; JS shows the pane.
    Route::get('/', 'app')->name('dashboard');
    Route::get('/orders', 'app')->name('orders');
    Route::get('/credits', 'app')->name('credits');
    Route::get('/profile', 'app')->name('profile');
});
