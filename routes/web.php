<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Public\DemoRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// PWA manifest — served through PHP so the Content-Type is always
// `application/manifest+json` (the web server may not map the .webmanifest
// extension, and a wrong MIME stops Safari from treating the app as installable).
Route::get('/manifest.webmanifest', function () {
    return response()
        ->file(public_path('assets/pwa/manifest.json'), [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
})->name('pwa.manifest');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch');

Route::get('/', function () {
    if (Auth::guard('customer')->check()) {
        return redirect()->route('customer.dashboard');
    }

    if (! Auth::check()) {
        return view('public.home');
    }

    $user = Auth::user();

    return redirect()->route($user->defaultDashboardRouteName());
})->name('home');

Route::post('/demo-request', [DemoRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('demo.request.store');
