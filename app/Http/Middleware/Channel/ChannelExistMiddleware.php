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
        $workspaceId = data_get($request, 'workspace_id');

        if ($channelId !== '') {
            // Get specific channel
            $channel = Channel::where('_id', $channelId)->first();

            if (!$channel) {
                return response()->notFound('Channel not found.');
            }

            $request->merge(['channel' => $channel]);
            $request->attributes->set('channel', $channel);

            return $next($request);
        }

        // For listing channels - get channels where user is creator OR member
        $user = $request->user();
        $userId = (string) $user->_id;
        
        $channels = Channel::where('workspace_id', $workspaceId)
            ->where(function($query) use ($userId) {
                $query->where('created_id', $userId) // User is creator
                      ->orWhereRaw('JSON_CONTAINS(members, ?0, ?1)', [$userId]); // User is in members array
            })
            ->get();

        if ($channels->isEmpty()) {
            abort(404, 'No channels found for this workspace.');
        }

        $request->merge(['channels' => $channels]);
        $request->attributes->set('channels', $channels);

        return $next($request);
    }
}