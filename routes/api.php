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

// Admin routes
Route::prefix('admin/workspaces')->group(base_path('routes/admin/workspaces.php'));
Route::prefix('admin/teams')->group(base_path('routes/admin/teams.php'));
Route::prefix('admin/channels')->group(base_path('routes/admin/channels.php'));
Route::prefix('admin/messages')->group(base_path('routes/admin/messages.php'));
Route::prefix('admin/users')->group(base_path('routes/admin/users.php'));
Route::prefix('admin/impersonate')->group(base_path('routes/admin/impersonate.php'));
