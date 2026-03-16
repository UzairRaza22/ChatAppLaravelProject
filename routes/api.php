<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\Api\SocialAuthController; 

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

Route::middleware(['api'])->group(function () {

    // Auth Group ke andar Google Routes add kiye hain
    Route::prefix('auth')->group(function () {
        // Google Login Routes
        Route::get('google', [SocialAuthController::class, 'redirectToGoogle']);
        Route::get('google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
        
        // Purani auth routes file
        require base_path('routes/auth.php');
    });

    Route::prefix('workspaces')->group(base_path('routes/workspaces.php'));
    Route::prefix('team')->group(base_path('routes/team.php'));
    Route::prefix('messages')->group(base_path('routes/Messages.php'));
    Route::prefix('channels')->group(base_path('routes/channel.php'));

    Route::post('/signup', [AuthController::class, 'signup']);

});

// 3. Fcm routes
require base_path('routes/Fcm.php');