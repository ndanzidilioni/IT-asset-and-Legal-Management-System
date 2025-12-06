<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    
    // User management routes
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
    Route::get('developer-overview', [UserController::class, 'developerOverview']);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::post('users/{user}/change-password', [UserController::class, 'changePassword']);
});

// Health check
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'user-management',
        'timestamp' => now()
    ]);
});














