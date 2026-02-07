<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

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

    // Clients
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::put('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);

    // Time Entries
    Route::get('/time-entries', [TimeEntryController::class, 'index']);
    Route::post('/time-entries', [TimeEntryController::class, 'store']);
    Route::get('/time-entries/{timeEntry}', [TimeEntryController::class, 'show']);
    Route::put('/time-entries/{timeEntry}', [TimeEntryController::class, 'update']);
    Route::delete('/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy']);

    // Clock in/out
    Route::post('/time-entries/start', [TimeEntryController::class, 'start']);
    Route::post('/time-entries/stop', [TimeEntryController::class, 'stop']);

    // QR Code validation (public for mobile app)
    Route::post('/qr-codes/validate', [\App\Http\Controllers\QRCodeController::class, 'validate']);

    // Team management (Responsable manages Gestionnaires)
    Route::prefix('team')->group(function () {
        Route::get('/', [\App\Http\Controllers\TeamController::class, 'index']);
        Route::get('/available', [\App\Http\Controllers\TeamController::class, 'available']);
        Route::post('/{user}', [\App\Http\Controllers\TeamController::class, 'attach']);
        Route::delete('/{user}', [\App\Http\Controllers\TeamController::class, 'detach']);
    });

    // Admin routes
    Route::middleware(\App\Http\Middleware\EnsureUserIsAdmin::class)->prefix('admin')->name('admin.')->group(function () {
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
        Route::delete('/logs/application', [LogController::class, 'clearApplicationLogs']);
        Route::get('/logs/activity', [LogController::class, 'getActivityLogs']);
        Route::get('/logs/statistics', [LogController::class, 'getLogStatistics']);

        // Projects management (extend existing routes)
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        
        // QR Code generation for projects
        Route::post('/projects/{project}/qr-code/generate', [\App\Http\Controllers\QRCodeController::class, 'generateToken']);
        Route::get('/projects/{project}/qr-code', [\App\Http\Controllers\QRCodeController::class, 'getQRCode']);
    });
});
