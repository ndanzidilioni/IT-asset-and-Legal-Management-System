<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Session (cookie) based auth endpoints for SPA
Route::post('/login-web', [AuthController::class, 'loginWeb']);
Route::post('/logout-web', [AuthController::class, 'logoutWeb']);

// Fallback login route for API redirects (returns JSON instead of redirecting)
Route::get('/login', function () {
    return response()->json([
        'message' => 'Unauthenticated.'
    ], 401);
})->name('login');

// Explicit OPTIONS routes for CORS preflight
Route::options('/login-web', function () {
    return response('', 200);
});
Route::options('/logout-web', function () {
    return response('', 200);
});

// Debug route to check session and CSRF
Route::get('/debug-session', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token(),
        'session_data' => session()->all(),
        'cookies' => request()->cookies->all(),
        'headers' => request()->headers->all(),
    ]);
});
