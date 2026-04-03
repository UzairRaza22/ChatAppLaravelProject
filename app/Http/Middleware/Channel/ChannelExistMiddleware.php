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
        $user = data_get($request, 'user');
        
        // Check if user is authenticated
        if (!$user) {
            return response()->unauthorized('User not authenticated.');
        }
        
        $userId = (string) data_get($user, '_id');
        
        // Check if channel_id is provided in request body or route parameter
        $channelId = (string) ($request->route('id') ?? $request->input('channel_id') ?? $request->query('channel_id'));
        
        if ($channelId !== '') {
            // Get specific channel - check if user is creator OR member
            $channel = Channel::where('_id', $channelId)
                ->where(function ($query) use ($userId) {
                    $query->where('created_id', $userId)  // User is creator
                          ->orWhere('members', 'elemMatch', ['user_id' => $userId]);  // OR user is member
                })
                ->first();
                
            if (!$channel) {
                return response()->notFound('Channel not found or access denied.');
            }
            
            // Set both channel and channels for flexibility
            $request->merge([
                'channel' => $channel,
                'channels' => collect([$channel])
            ]);
            $request->attributes->set('channel', $channel);
            $request->attributes->set('channels', collect([$channel]));
        } else {
            // Get all channels for user - where user is creator OR member
            $channels = Channel::where(function ($query) use ($userId) {
                $query->where('created_id', $userId)  // User is creator
                      ->orWhere('members', 'elemMatch', ['user_id' => $userId]);  // OR user is member
            })->get();
            
            // Set channels for all channels (no single channel property)
            $request->merge(['channels' => $channels]);
            $request->attributes->set('channels', $channels);
        }

        return $next($request);
    }
}