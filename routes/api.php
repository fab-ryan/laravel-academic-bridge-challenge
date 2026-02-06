<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth routes
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/user', [AuthController::class, 'user'])->name('user');
        });

        // Employee CRUD routes
        Route::apiResource('employees', EmployeeController::class)->names('employees');

        // Attendance routes
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
            Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
            Route::get('/employee/{employee}/today', [AttendanceController::class, 'todayAttendance'])->name('employee.today');
            Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
        });

        // Report routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/attendance/pdf', [ReportController::class, 'attendancePdf'])->name('attendance.pdf');
            Route::get('/attendance/excel', [ReportController::class, 'attendanceExcel'])->name('attendance.excel');
        });
    });
});


/*
|--------------------------------------------------------------------------
| API Information Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'Academic Bridge API'),
        'version' => '1.0.0',
        'documentation' => url('/api/documentation'),
        'message' => 'Welcome to the API. Please refer to the documentation for usage details.',
    ]);
})->name('api.info');

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found. Please check the API documentation.',
    ], 404);
});
