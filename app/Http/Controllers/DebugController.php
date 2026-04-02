<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function channelDebug(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'No authenticated user found',
                    'suggestion' => 'Make sure to include Authorization: Bearer YOUR_TOKEN'
                ], 401);
            }

            $userId = (string) $user->_id;
            
            // Get all channels
            $allChannels = Channel::all();
            
            // Debug information
            $debug = [
                'user_info' => [
                    'id' => $userId,
                    'name' => $user->name ?? 'N/A',
                    'email' => $user->email ?? 'N/A',
                    'id_type' => gettype($userId),
                    'id_length' => strlen($userId)
                ],
                'total_channels' => $allChannels->count(),
                'channels_analysis' => [],
                'user_channels' => [],
                'filtering_results' => []
            ];

            // Analyze each channel
            foreach ($allChannels as $channel) {
                $isCreator = (string) $channel->created_id === $userId;
                $isMember = false;
                $userRole = null;
                
                // Check if user is member
                foreach ($channel->members ?? [] as $member) {
                    if (is_array($member) && isset($member['user_id'])) {
                        if ((string) $member['user_id'] === $userId) {
                            $isMember = true;
                            $userRole = $member['role'] ?? 'unknown';
                            break;
                        }
                    }
                }
                
                $shouldShow = $isCreator || $isMember;
                
                $channelAnalysis = [
                    'id' => $channel->_id,
                    'name' => $channel->name,
                    'created_by' => $channel->created_id,
                    'created_by_type' => gettype($channel->created_id),
                    'members_raw' => $channel->members,
                    'members_count' => is_array($channel->members) ? count($channel->members) : 0,
                    'user_is_creator' => $isCreator,
                    'user_is_member' => $isMember,
                    'user_role' => $userRole,
                    'should_show_to_user' => $shouldShow
                ];
                
                $debug['channels_analysis'][] = $channelAnalysis;
                
                if ($shouldShow) {
                    $debug['user_channels'][] = [
                        'id' => $channel->_id,
                        'name' => $channel->name,
                        'access_type' => $isCreator ? 'creator' : 'member',
                        'role' => $userRole
                    ];
                }
            }
            
            // Test the actual filtering logic
            $filteredChannels = $allChannels->filter(function ($channel) use ($userId) {
                // Check if user is creator
                if ((string) $channel->created_id === $userId) {
                    return true;
                }
                
                // Check if user is member
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
            
            $debug['filtering_results'] = [
                'filtered_count' => $filteredChannels->count(),
                'expected_count' => count($debug['user_channels']),
                'filtering_works' => $filteredChannels->count() === count($debug['user_channels'])
            ];
            
            return response()->json([
                'success' => true,
                'debug_info' => $debug,
                'summary' => [
                    'total_channels' => $allChannels->count(),
                    'user_accessible_channels' => count($debug['user_channels']),
                    'filtering_working' => $debug['filtering_results']['filtering_works']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug failed',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    public function messageDebug(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'No authenticated user'], 401);
            }
            
            $channelId = $request->input('channel_id');
            if (!$channelId) {
                return response()->json(['error' => 'channel_id required'], 400);
            }
            
            $channel = Channel::where('_id', $channelId)->first();
            if (!$channel) {
                return response()->json(['error' => 'Channel not found'], 404);
            }
            
            $userId = (string) $user->_id;
            $isCreator = (string) $channel->created_id === $userId;
            $isMember = false;
            $userRole = null;
            
            // Check if user is member
            foreach ($channel->members ?? [] as $member) {
                if (is_array($member) && isset($member['user_id'])) {
                    if ((string) $member['user_id'] === $userId) {
                        $isMember = true;
                        $userRole = $member['role'] ?? 'unknown';
                        break;
                    }
                }
            }
            
            $canAccess = $isCreator || $isMember;
            
            return response()->json([
                'success' => true,
                'channel_info' => [
                    'id' => $channel->_id,
                    'name' => $channel->name,
                    'created_by' => $channel->created_id,
                    'members' => $channel->members
                ],
                'user_access' => [
                    'user_id' => $userId,
                    'is_creator' => $isCreator,
                    'is_member' => $isMember,
                    'role' => $userRole,
                    'can_access' => $canAccess
                ],
                'message' => $canAccess ? 'User can access this channel' : 'User cannot access this channel'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Message debug failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}