<?php

use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\CreditController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Customer\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer API (v1)
|--------------------------------------------------------------------------
|
| Stateless JSON API consumed by the web customer portal (axios) and the
| future Flutter app — both use the exact same endpoints. Authentication is
| Sanctum Bearer-token; every authenticated request is scoped to the
| customer's shop by the customer.tenant.init middleware.
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

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');

        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');

        Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    });
});
