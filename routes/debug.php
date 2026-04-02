<?php

use App\Http\Controllers\DebugController;
use Illuminate\Support\Facades\Route;

// Debug routes - remove these in production
Route::middleware(['check.token:login_token', 'check.active'])->group(function () {
    
    // Debug channel access
    Route::get('/debug/channels', [DebugController::class, 'channelDebug']);
    
    // Debug message access for specific channel
    Route::post('/debug/message-access', [DebugController::class, 'messageDebug']);
    
    // Test the actual middleware flow
    Route::get('/debug/test-middleware', function(\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'No user found'], 401);
        }
        
        // Simulate the ChannelExistMiddleware logic
        $userId = (string) $user->_id;
        
        // Test 1: Raw MongoDB query
        try {
            $rawChannels = \App\Models\Channel::raw(function($collection) use ($userId) {
                return $collection->find([
                    '$or' => [
                        ['created_id' => $userId],
                        ['members.user_id' => $userId],
                        ['members' => $userId]
                    ]
                ]);
            });
            $rawCount = count($rawChannels);
        } catch (\Exception $e) {
            $rawCount = 'Error: ' . $e->getMessage();
        }
        
        // Test 2: Eloquent query
        try {
            $eloquentChannels = \App\Models\Channel::where('created_id', $userId)->get();
            $eloquentCount = $eloquentChannels->count();
        } catch (\Exception $e) {
            $eloquentCount = 'Error: ' . $e->getMessage();
        }
        
        // Test 3: PHP filtering
        try {
            $allChannels = \App\Models\Channel::all();
            $filteredChannels = $allChannels->filter(function ($channel) use ($userId) {
                if ((string) $channel->created_id === $userId) {
                    return true;
                }
                
                $members = $channel->members ?? [];
                foreach ($members as $member) {
                    if (is_array($member) && isset($member['user_id'])) {
                        if ((string) $member['user_id'] === $userId) {
                            return true;
                        }
                    }
                }
                return false;
            });
            $filteredCount = $filteredChannels->count();
        } catch (\Exception $e) {
            $filteredCount = 'Error: ' . $e->getMessage();
        }
        
        return response()->json([
            'user_id' => $userId,
            'total_channels' => \App\Models\Channel::count(),
            'test_results' => [
                'raw_mongodb_query' => $rawCount,
                'eloquent_creator_query' => $eloquentCount,
                'php_filtering' => $filteredCount
            ]
        ]);
    });
    
    // Create test data
    Route::post('/debug/create-test-data', function(\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'No user found'], 401);
        }
        
        $userId = (string) $user->_id;
        
        // Create a channel where user is creator
        $creatorChannel = \App\Models\Channel::updateOrCreate(
            ['name' => 'Debug Creator Channel'],
            [
                'workspace_id' => '507f1f77bcf86cd799439011',
                'team_id' => '507f1f77bcf86cd799439014',
                'type' => 'public',
                'created_id' => $userId,
                'members' => [
                    [
                        'user_id' => $userId,
                        'role' => 'admin'
                    ]
                ]
            ]
        );
        
        // Create a channel where user is member
        $memberChannel = \App\Models\Channel::updateOrCreate(
            ['name' => 'Debug Member Channel'],
            [
                'workspace_id' => '507f1f77bcf86cd799439011',
                'team_id' => '507f1f77bcf86cd799439014',
                'type' => 'private',
                'created_id' => '507f1f77bcf86cd799439999',
                'members' => [
                    [
                        'user_id' => '507f1f77bcf86cd799439999',
                        'role' => 'admin'
                    ],
                    [
                        'user_id' => $userId,
                        'role' => 'member'
                    ]
                ]
            ]
        );
        
        return response()->json([
            'success' => true,
            'created_channels' => [
                'creator_channel' => [
                    'id' => $creatorChannel->_id,
                    'name' => $creatorChannel->name,
                    'created_by' => $creatorChannel->created_id,
                    'members' => $creatorChannel->members
                ],
                'member_channel' => [
                    'id' => $memberChannel->_id,
                    'name' => $memberChannel->name,
                    'created_by' => $memberChannel->created_id,
                    'members' => $memberChannel->members
                ]
            ]
        ]);
    });
});