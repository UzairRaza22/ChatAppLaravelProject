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

        // Validate workspace_id is provided
        if (empty($workspaceId)) {
            return response()->error('workspace_id is required.', null, 400);
        }

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
                      ->orWhere('members', 'regex', new \MongoDB\BSON\Regex($userId)); // User is in members array
            })
            ->get();

        if ($channels->isEmpty()) {
            return response()->notFound('No channels found for this workspace.');
        }

        $request->merge(['channels' => $channels]);
        $request->attributes->set('channels', $channels);

        return $next($request);
    }
}