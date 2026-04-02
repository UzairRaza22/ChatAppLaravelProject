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

// Load modular route files
Route::prefix('auth')->group(base_path('routes/auth.php'));
Route::prefix('workspaces')->group(base_path('routes/workspaces.php'));
Route::prefix('team')->group(base_path('routes/team.php'));
Route::prefix('messages')->group(base_path('routes/Messages.php'));
Route::prefix('channels')->group(base_path('routes/channel.php'));

// Temporary test route - REMOVE after debugging
require base_path('routes/test-channel.php');
Route::get('test-webhook', function () {
    $webhookUrl = env('ALERT_WEBHOOK_URL');

    if (!$webhookUrl) {
        return response()->json(['success' => false, 'message' => 'ALERT_WEBHOOK_URL is not set in .env'], 500);
    }

    $payload = [
        'app'       => config('app.name'),
        'env'       => app()->environment(),
        'exception' => 'TestException',
        'message'   => 'This is a test webhook from Laravel',
        'time'      => now()->toIso8601String(),
    ];

    try {
        \Illuminate\Support\Facades\Http::timeout(5)->post($webhookUrl, $payload);
        return response()->json(['success' => true, 'message' => 'Webhook sent!', 'payload' => $payload]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => 'Failed to send webhook: ' . $e->getMessage()], 500);
    }
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