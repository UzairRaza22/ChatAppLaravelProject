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

// Simple test to verify deployment
Route::get('/deployment-test', function () {
    return response()->json([
        'message' => 'Deployment successful',
        'timestamp' => now()->toISOString()
    ]);
});

// Simple debug endpoint without auth to check basic structure
Route::get('/debug-channels-simple', function() {
    try {
        $allChannels = \App\Models\Channel::all();
        
        $result = [
            'total_channels' => $allChannels->count(),
            'sample_channels' => []
        ];
        
        // Show first 3 channels structure
        foreach ($allChannels->take(3) as $channel) {
            $result['sample_channels'][] = [
                'id' => $channel->_id,
                'name' => $channel->name,
                'created_by' => $channel->created_id,
                'members_raw' => $channel->members,
                'members_count' => count($channel->members ?? [])
            ];
        }
        
        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Load modular route files
Route::prefix('auth')->group(base_path('routes/auth.php'));
Route::prefix('workspaces')->group(base_path('routes/workspaces.php'));
Route::prefix('team')->group(base_path('routes/team.php'));
Route::prefix('messages')->group(base_path('routes/Messages.php'));
Route::prefix('channels')->group(base_path('routes/channel.php'));




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

    // Debug endpoint with authentication
    Route::middleware(['check.token:login_token', 'check.active'])->get('/debug-channels', function(\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'No user found'], 401);
        }
        
        $userId = (string) $user->_id;
        
        // Get all channels to analyze structure
        $allChannels = \App\Models\Channel::all();
        
        $result = [
            'user_id' => $userId,
            'user_name' => $user->name ?? 'N/A',
            'total_channels' => $allChannels->count(),
            'channels_analysis' => [],
            'query_tests' => []
        ];
        
        // Analyze each channel
        foreach ($allChannels as $channel) {
            $isCreator = (string) $channel->created_id === $userId;
            $memberDetails = null;
            
            // Test different member query approaches
            $members = $channel->members ?? [];
            
            // Method 1: Check if members.user_id matches
            $method1 = false;
            if (is_array($members)) {
                foreach ($members as $member) {
                    if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId) {
                        $method1 = true;
                        $memberDetails = $member;
                        break;
                    }
                }
            }
            
            // Method 2: Check raw structure
            $method2 = in_array($userId, array_column($members, 'user_id'));
            
            $channelInfo = [
                'id' => $channel->_id,
                'name' => $channel->name,
                'created_by' => $channel->created_id,
                'members_raw' => $members,
                'members_count' => count($members),
                'user_is_creator' => $isCreator,
                'member_check_method1' => $method1,
                'member_check_method2' => $method2,
                'member_details' => $memberDetails,
                'should_show' => $isCreator || $method1 || $method2
            ];
            
            $result['channels_analysis'][] = $channelInfo;
        }
        
        // Test different MongoDB queries
        try {
            $result['query_tests'] = [
                'creator_query' => \App\Models\Channel::where('created_id', $userId)->count(),
                'members_user_id_query' => \App\Models\Channel::where('members.user_id', $userId)->count(),
                'combined_query' => \App\Models\Channel::where(function ($query) use ($userId) {
                    $query->where('created_id', $userId)
                          ->orWhere('members.user_id', $userId);
                })->count()
            ];
        } catch (\Exception $e) {
            $result['query_error'] = $e->getMessage();
        }
        
        return response()->json($result);
    });

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