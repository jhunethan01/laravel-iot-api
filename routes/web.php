<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Non-API web routes, useful for some incoming webhooks

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
