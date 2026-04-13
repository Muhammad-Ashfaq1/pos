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
    Route::post('/login', 'loginSubmit')->name('login.submit');

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
