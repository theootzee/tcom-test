<?php

use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\CorsMiddleware;

Route::middleware(CorsMiddleware::class)->group(function () {
        Route::apiResource('users', UsersController::class)->middleware(['auth:sanctum','ability:admin']);
        Route::apiResource('teams', TeamController::class)->middleware(['auth:sanctum','ability:admin']);
    Route::post('/login', LoginController::class);
});

