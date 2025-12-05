<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitLogging;
use App\Http\Middleware\JWTVerification;

Route::middleware([InitLogging::class])->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('api.login');
    Route::middleware([JWTVerification::class])->group(function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
            Route::get('me', [AuthController::class, 'me'])->name('api.me');
        });
    });
});
