<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ----------------------
// Guest routes (login/register/forgot)
// ----------------------
Route::middleware('guest')->controller(AuthController::class)->group(function () {

    // Register
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'store')->name('register.store');

    // Login
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'loginSubmit')->name('login.submit')->middleware('throttle:5,1');

    // Forgot password
    Route::get('/forgot', 'forgot')->name('forgot');
    Route::post('/forgot', 'sendResetLink')->name('password.email');

    // Reset password
    Route::get('/reset-password/{token}', 'resetForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

// ----------------------
// Authenticated routes
// ----------------------
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

// ----------------------
// Email verification
// ----------------------
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

 use App\Http\Controllers\Admin\TenantController;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        // =========================
        // SHOP / TENANT MANAGEMENT
        // =========================

        Route::get('/shops', [TenantController::class, 'index'])
            ->name('shops.index');

        Route::get('/shops/pending', [TenantController::class, 'pending'])
            ->name('shops.pending');

      
        Route::post('/shops/approve/{id}', [TenantController::class, 'approve'])
            ->name('shops.approve');

      
        Route::post('/shops/reject/{id}', [TenantController::class, 'reject'])
            ->name('shops.reject');

      
        Route::post('/shops/suspend/{id}', [TenantController::class, 'suspend'])
            ->name('shops.suspend');

        Route::post('/shops/reactivate/{id}', [TenantController::class, 'reactivate'])
            ->name('shops.reactivate');

   
     

        Route::get('/shops/impersonate/{id}', [TenantController::class, 'impersonate'])
            ->name('shops.impersonate');

        Route::get('/impersonate/stop', [TenantController::class, 'stopImpersonate'])
            ->name('impersonate.stop');
    });