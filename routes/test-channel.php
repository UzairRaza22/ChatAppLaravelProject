<?php

use App\Models\Channel;
use Illuminate\Support\Facades\Route;

// Temporary test route - REMOVE after debugging
Route::middleware(['check.token:login_token', 'check.active'])->group(function () {
    Route::get('/test-channels', function(\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'No user found'], 401);
        }
        
        $userId = (string) $user->_id;
        
        // Get all channels
        $allChannels = Channel::all();
        
        $result = [
            'user_id' => $userId,
            'user_name' => $user->name ?? 'N/A',
            'total_channels' => $allChannels->count(),
            'channels_analysis' => [],
            'user_channels' => []
        ];
        
        // Analyze each channel
        foreach ($allChannels as $channel) {
            $isCreator = (string) $channel->created_id === $userId;
            $isMember = false;
            $memberDetails = null;
            
            // Check membership
            if (is_array($channel->members)) {
                foreach ($channel->members as $index => $member) {
                    $memberUserId = null;
                    
                    if (is_array($member) && isset($member['user_id'])) {
                        $memberUserId = (string) $member['user_id'];
                        if ($memberUserId === $userId) {
                            $isMember = true;
                            $memberDetails = $member;
                            break;
                        }
                    } elseif (is_object($member) && isset($member->user_id)) {
                        $memberUserId = (string) $member->user_id;
                        if ($memberUserId === $userId) {
                            $isMember = true;
                            $memberDetails = $member;
                            break;
                        }
                    } elseif (is_string($member)) {
                        if ((string) $member === $userId) {
                            $isMember = true;
                            $memberDetails = $member;
                            break;
                        }
                    }
                }
            }
            
            $shouldShow = $isCreator || $isMember;
            
            $channelInfo = [
                'id' => $channel->_id,
                'name' => $channel->name,
                'created_by' => $channel->created_id,
                'members_raw' => $channel->members,
                'user_is_creator' => $isCreator,
                'user_is_member' => $isMember,
                'member_details' => $memberDetails,
                'should_show' => $shouldShow
            ];
            
            $result['channels_analysis'][] = $channelInfo;
            
            if ($shouldShow) {
                $result['user_channels'][] = [
                    'id' => $channel->_id,
                    'name' => $channel->name,
                    'access_type' => $isCreator ? 'creator' : 'member'
                ];
            }
        }
        
        return response()->json($result);
    });
});