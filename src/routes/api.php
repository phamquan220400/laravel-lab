<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitLogging;

Route::middleware(['logging'])->group(function () {
    Route::get('/a', function () {
        return response()->json(['message' => 'Hello, World!']);
    })->name('api.hello');
    Route::post('login', [AuthController::class, 'login'])->name('api.login');
    Route::middleware(['jwt.verify'])->group(function () {
    });
});