<?php

use Illuminate\Routing\Route;
use Sicaboy\LaravelDashboard\Http\Controllers\DashboardController;

Route::prefix('v1')->group(function () {
    Route::get('dashboard/{identity}', [DashboardController::class, 'getDashboardMeta']);
    Route::post('dashboard/{identity}', [DashboardController::class, 'getDashboard']);
});
