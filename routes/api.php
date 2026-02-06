<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AttendanceController;

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

    // Employee CRUD routes
    Route::apiResource('employees', EmployeeController::class);


    // Attendance routes
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendances/employee/{employee}/today', [AttendanceController::class, 'todayAttendance']);
    Route::get('/attendances/{attendance}', [AttendanceController::class, 'show']);
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
