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
Route::prefix('team')->group(base_path('routes/team.php'));
Route::prefix('messages')->group(base_path('routes/Messages.php'));
Route::prefix('channels')->group(base_path('routes/channel.php'));


require base_path('routes/Fcm.php');
