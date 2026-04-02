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
            // Use a completely different approach - raw MongoDB aggregation
            try {
                // First, try to get channels where user is creator
                $creatorChannels = Channel::where('created_id', $userId)->get();
                
                // Then, get channels where user is member using raw MongoDB query
                $memberChannels = Channel::raw(function($collection) use ($userId) {
                    return $collection->find([
                        '$or' => [
                            ['members.user_id' => $userId],
                            ['members' => $userId], // fallback for string arrays
                        ]
                    ]);
                });
                
                // Convert raw results to Eloquent models
                $memberChannelModels = collect($memberChannels)->map(function($channelData) {
                    return new Channel($channelData);
                });
                
                // Combine results
                $allUserChannels = $creatorChannels->merge($memberChannelModels)->unique('_id');
                
                // If raw query fails, fallback to PHP filtering
                if ($allUserChannels->count() === 0) {
                    $allChannels = Channel::all();
                    $allUserChannels = $allChannels->filter(function ($channel) use ($userId) {
                        // Check creator
                        if ((string) $channel->created_id === $userId) {
                            return true;
                        }
                        
                        // Check members with multiple approaches
                        $members = $channel->members ?? [];
                        
                        // Handle array of objects
                        if (is_array($members)) {
                            foreach ($members as $member) {
                                // Object with user_id field
                                if (is_array($member) && isset($member['user_id'])) {
                                    if ((string) $member['user_id'] === $userId) {
                                        return true;
                                    }
                                }
                                // Object as stdClass
                                elseif (is_object($member) && property_exists($member, 'user_id')) {
                                    if ((string) $member->user_id === $userId) {
                                        return true;
                                    }
                                }
                                // Simple string
                                elseif (is_string($member) && (string) $member === $userId) {
                                    return true;
                                }
                            }
                        }
                        
                        return false;
                    });
                }
                
                $channels = $allUserChannels->values();
                
            } catch (\Exception $e) {
                // Ultimate fallback - simple creator query only
                $channels = Channel::where('created_id', $userId)->get();
            }
            
            data_set($request, 'channels', $channels);
            $request->attributes->set('channels', $channels);

            return $next($request);
        }

        return response()->error('channel_id or user_id is required');
    }
}
