<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ThemePreferencesController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'store')->name('register.store');

    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'loginSubmit')->middleware('throttle:5,1')->name('login.submit');

    Route::get('/forgot', 'forgot')->name('forgot');
    Route::post('/forgot', 'sendResetLink')->name('password.email');

    Route::get('/reset-password/{token}', 'resetForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth:web,customer', 'account.context'])
    ->controller(AccountSettingsController::class)
    ->group(function () {
        Route::get('/account/profile', 'profile')->name('account.profile');
        Route::post('/account/profile', 'updateProfile')->name('account.profile.update');
        Route::get('/account/password', 'password')->name('account.password');
        Route::post('/account/password', 'updatePassword')->name('account.password.update');
    });

Route::middleware('auth')->group(function () {
    Route::put('/account/theme', [ThemePreferencesController::class, 'update'])
        ->name('account.theme.update');
    Route::put('/account/theme/tenant', [ThemePreferencesController::class, 'updateTenant'])
        ->name('account.theme.tenant.update');
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');
