<?php

use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\CreditController;
use App\Http\Controllers\Api\Customer\DashboardController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Customer\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer API (v1)
|--------------------------------------------------------------------------
|
| Stateless JSON API consumed by the web customer portal (axios CDN) and the
| Flutter app — both use the exact same endpoints + params. Authentication is
| Sanctum Bearer-token; every authenticated request is scoped to the
| customer's shop by the customer.tenant.init middleware.
|
| Auth (guest):
|   POST /api/v1/customer/login            { email, password, device_name?, shop? }
|   POST /api/v1/customer/register         { shop, name, email, phone?, password, password_confirmation }
|   POST /api/v1/customer/forgot-password  { shop, email }
|   POST /api/v1/customer/reset-password   { shop, email, token, password, password_confirmation }
|
| Auth (Bearer):
|   POST /api/v1/customer/logout
|   GET  /api/v1/customer/me
|   PATCH /api/v1/customer/me              { name?, phone?, address? }
|   GET  /api/v1/customer/dashboard        ?recent_limit=5  (web + Flutter overview)
|   GET  /api/v1/customer/orders           ?per_page=15&page=1
|   GET  /api/v1/customer/orders/{id}
|   GET  /api/v1/customer/credits          ?type=earn|redeem|adjust|expire&per_page=20&page=1
|   GET  /api/v1/customer/vehicles
|
*/

Route::prefix('v1/customer')->name('api.v1.customer.')->group(function (): void {
    // Guest (tenant resolved from the `shop` slug in the request body).
    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    });

    // Authenticated portal endpoints.
    Route::middleware(['auth:sanctum', 'customer.tenant.init'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/me', [ProfileController::class, 'show'])->name('me');
        Route::patch('/me', [ProfileController::class, 'update'])->name('me.update');

        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');

        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');

        Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    });
});
