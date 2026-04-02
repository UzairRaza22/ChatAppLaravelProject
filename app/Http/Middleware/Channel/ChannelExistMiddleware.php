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

        // If specific channel_id is provided, fetch that channel
        if ($channelId !== '') {
            $channel = Channel::where('_id', $channelId)->first();

            if (!$channel) {
                return response()->notFound('Channel not found.');
            }

            data_set($request, 'channel', $channel);
            $request->attributes->set('channel', $channel);

            return $next($request);
        }

        // Get user ID from token if not provided
        if ($userId === '') {
            $user = $request->user() ?? $request->input('user') ?? $request->input('verified_user');
            $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id'));

            if ($userId === '') {
                $tokenRecord = $request->input('token_record');
                $userId = (string) data_get($tokenRecord, 'user_id');
            }
        }

        if ($userId !== '') {
            // Use MongoDB query to find channels where user is creator OR member
            $channels = Channel::where(function ($query) use ($userId) {
                // User is creator
                $query->where('created_id', $userId)
                      // OR user is in members array (handle different member structures)
                      ->orWhere('members.user_id', $userId)  // For object structure: {"user_id": "...", "role": "..."}
                      ->orWhere('members', $userId);         // For simple string structure
            })->get();
            
            data_set($request, 'channels', $channels);
            $request->attributes->set('channels', $channels);

            return $next($request);
        }

        return response()->error('channel_id or user_id is required');
    }
}
