<?php

use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\CorsMiddleware;

Route::middleware(CorsMiddleware::class)->group(function () {
    Route::apiResource('users', UsersController::class)->middleware(['auth:sanctum','ability:admin']);
    Route::apiResource('teams', TeamController::class)->middleware(['auth:sanctum','ability:admin']);
    
    Route::put('/leave/respond/{id}', [LeaveController::class, 'respond'])->middleware(['auth:sanctum','ability:manager']);

    Route::apiResource('leave', LeaveController::class)->middleware(['auth:sanctum','ability:manager,employee']);
    Route::get('/leave/showTeamLeaves/{user_id}/{team_id}', [LeaveController::class, 'showTeamLeaves'])->middleware(['auth:sanctum','ability:employee,manager']);
    Route::get('/leave/getLeaveHistory/{id}', [LeaveController::class, 'getLeaveHistory'])->middleware(['auth:sanctum','ability:employee,manager']);

    Route::post('/changePassword', [PasswordController::class, 'changePassword'])->middleware(['auth:sanctum','ability:employee,manager,admin']);

    Route::post('/forgotPassword', [PasswordController::class, 'forgotPassword']);

    Route::post('/login', LoginController::class);
    
});