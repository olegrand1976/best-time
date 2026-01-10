<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store'])->middleware('can:create,App\Models\Project');

    // Time Entries
    Route::get('/time-entries', [TimeEntryController::class, 'index']);
    Route::post('/time-entries', [TimeEntryController::class, 'store']);
    Route::get('/time-entries/{timeEntry}', [TimeEntryController::class, 'show']);
    Route::put('/time-entries/{timeEntry}', [TimeEntryController::class, 'update']);
    Route::delete('/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy']);

    // Clock in/out
    Route::post('/time-entries/start', [TimeEntryController::class, 'start']);
    Route::post('/time-entries/stop', [TimeEntryController::class, 'stop']);

    // Admin routes
    Route::middleware(['can:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Users management
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/users/{user}/statistics', [UserController::class, 'statistics']);

        // Statistics
        Route::get('/statistics', [StatisticsController::class, 'index']);

        // Logs
        Route::get('/logs/application', [LogController::class, 'getApplicationLogs']);
        Route::get('/logs/activity', [LogController::class, 'getActivityLogs']);
        Route::get('/logs/statistics', [LogController::class, 'getLogStatistics']);

        // Projects management (extend existing routes)
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    });
});
