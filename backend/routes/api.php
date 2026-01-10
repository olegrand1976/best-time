<?php

declare(strict_types=1);

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
});
