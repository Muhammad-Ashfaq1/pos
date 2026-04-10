<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    // Register / Signup
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'store')->name('register.store');
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    // Register / Signup
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'store')->name('register.store');

    // Login
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'loginSubmit')->name('login.submit');

    // Forgot password
    Route::get('/forgot', 'forgot')->name('forgot');
    Route::post('/forgot', 'sendResetLink')->name('password.email');

    // Reset password (form + submit)
    Route::get('/reset-password/{token}', 'resetForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});
    // Login
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'loginSubmit')->name('login.submit');

    // Forgot password
    Route::get('/forgot', 'forgot')->name('forgot');
    Route::post('/forgot', 'sendResetLink')->name('password.email');

    // Reset password (form + submit)
    Route::get('/reset-password/{token}', 'resetForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

// ----------------------
// Auth (authenticated: logout)
// Auth (authenticated: logout)
// ----------------------

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

// ----------------------
// Email verification (signed link)
// Email verification (signed link)
// ----------------------

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');
