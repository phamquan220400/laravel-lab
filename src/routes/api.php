<?php

use App\Http\Controllers\Auth\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitLogging;
use App\Http\Middleware\JWTVerification;

Route::middleware([InitLogging::class])->group(function () {
    // Public routes
    Route::post('login', [AuthController::class, 'login'])->name('api.login');
    
    // Protected routes
    Route::middleware([JWTVerification::class])->group(function () {
        // Authenticated routes
        Route::group(['prefix' => 'auth'], function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
        });

        // User routes
        Route::group(['prefix' => 'users'], function () {
            Route::post('change-password', [AuthController::class, 'changePassword'])->name('api.change-password');
            Route::get('me', [AuthController::class, 'me'])->name('api.me');
        });
    });
});
