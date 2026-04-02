<?php

namespace App\Http\Middleware\Channel;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelExistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = (string) ($request->route('id') ?? $request->input('channel_id') ?? $request->query('channel_id'));
        $userId = (string) ($request->input('user_id') ?? $request->query('user_id'));

        if ($channelId !== '') {
            $channel = Channel::where('_id', $channelId)->first();

            if (!$channel) {
                return response()->notFound('Channel not found.');
            }

            data_set($request, 'channel', $channel);
            $request->attributes->set('channel', $channel);

            return $next($request);
        }

        if ($userId === '') {
            $user = $request->user() ?? $request->input('user') ?? $request->input('verified_user');
            $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id'));

            if ($userId === '') {
                $tokenRecord = $request->input('token_record');
                $userId = (string) data_get($tokenRecord, 'user_id');
            }
        }

        if ($userId !== '') {
            // Direct database query approach - no complex filtering
            $channels = collect();
            
            // Get channels where user is creator
            $creatorChannels = Channel::where('created_id', $userId)->get();
            $channels = $channels->merge($creatorChannels);
            
            // Get ALL channels and check each one manually for membership
            $allChannels = Channel::all();
            
            foreach ($allChannels as $channel) {
                // Skip if already included as creator
                if ((string) $channel->created_id === $userId) {
                    continue;
                }
                
                // Check if user is in members array
                $members = $channel->members;
                $isUserMember = false;
                
                if (is_array($members)) {
                    foreach ($members as $member) {
                        $memberUserId = null;
                        
                        // Extract user_id from different structures
                        if (is_array($member) && isset($member['user_id'])) {
                            $memberUserId = (string) $member['user_id'];
                        } elseif (is_object($member) && isset($member->user_id)) {
                            $memberUserId = (string) $member->user_id;
                        } elseif (is_string($member)) {
                            $memberUserId = (string) $member;
                        }
                        
                        // Compare user IDs
                        if ($memberUserId === $userId) {
                            $isUserMember = true;
                            break;
                        }
                    }
                }
                
                if ($isUserMember) {
                    $channels->push($channel);
                }
            }
            
            // Remove duplicates and convert to values
            $channels = $channels->unique('_id')->values();
            
            data_set($request, 'channels', $channels);
            $request->attributes->set('channels', $channels);

            return $next($request);
        }

        return response()->error('channel_id or user_id is required');
    }
}
