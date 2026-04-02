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
            // Simplified and more reliable approach
            $allChannels = Channel::all();
            
            $userChannels = $allChannels->filter(function ($channel) use ($userId) {
                // Check if user is creator
                if ((string) $channel->created_id === $userId) {
                    return true;
                }
                
                // Check if user is member
                $members = $channel->members ?? [];
                
                if (is_array($members)) {
                    foreach ($members as $member) {
                        // Handle array structure: {"user_id": "...", "role": "..."}
                        if (is_array($member) && isset($member['user_id'])) {
                            if ((string) $member['user_id'] === $userId) {
                                return true;
                            }
                        }
                        // Handle object structure
                        elseif (is_object($member) && property_exists($member, 'user_id')) {
                            if ((string) $member->user_id === $userId) {
                                return true;
                            }
                        }
                        // Handle simple string structure
                        elseif (is_string($member) && (string) $member === $userId) {
                            return true;
                        }
                    }
                }
                
                return false;
            });
            
            $channels = $userChannels->values();
            
            data_set($request, 'channels', $channels);
            $request->attributes->set('channels', $channels);

            return $next($request);
        }

        return response()->error('channel_id or user_id is required');
    }
}
