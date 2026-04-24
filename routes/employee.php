<?php

use App\Http\Controllers\Employee\PanelController;
use Illuminate\Support\Facades\Route;

Route::get('/employee/dashboard', [PanelController::class, 'dashboard'])
    ->name('employee.dashboard');
