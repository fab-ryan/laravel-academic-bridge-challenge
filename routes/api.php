<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public routes (no authentication required)
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);



/*|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
| Routes that require authentication
|*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});
Route::any("/", function () {
    return response()->json([
        'message' => 'Welcome to the API. Please refer to the documentation for usage details.'
    ]);
});
Route::any("/*", function () {
    return response()->json([
        'message' => 'Welcome to the API. Please refer to the documentation for usage details.'
    ]);
});
