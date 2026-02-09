<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



// Health check endpoint
Route::get('/health', function () {
    return [
        'success' => true,
        'data' => [
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'service' => 'Whistle IT API'
        ]
    ];
});

// Load modular route files
Route::prefix('auth')->group(base_path('routes/auth.php'));
Route::prefix('workspaces')->group(base_path('routes/workspaces.php'));
Route::prefix('teams')->group(base_path('routes/teams.php'));
Route::prefix('channels')->group(base_path('routes/channels.php'));
Route::prefix('messages')->group(base_path('routes/messages.php'));
Route::prefix('files')->group(base_path('routes/files.php'));

// Public users listing
Route::get('/users', [App\Http\Controllers\AuthController::class, 'users']);
