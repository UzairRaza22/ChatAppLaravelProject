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
        $channelId   = (string) ($request->route('id') ?? $request->input('channel_id') ?? $request->query('channel_id'));
        $workspaceId = data_get($request, 'workspace_id');

        // If a specific channel_id is provided — find by ID only, no workspace_id needed
        if ($channelId !== '') {
            $channel = Channel::where('_id', $channelId)->first();

            if (!$channel) {
                return response()->notFound('Channel not found.');
            }

            $request->merge(['channel' => $channel]);
            $request->attributes->set('channel', $channel);

            return $next($request);
        }

        // Listing channels — workspace_id is required
        if (empty($workspaceId)) {
            return response()->error('workspace_id is required.', 400);
        }

        $user   = $request->user();
        $userId = (string) $user->_id;

        $workspaceChannels = Channel::where('workspace_id', $workspaceId)->get();

        if ($workspaceChannels->isEmpty()) {
            return response()->notFound('Workspace not found.');
        }

        $channels = Channel::where('workspace_id', $workspaceId)
            ->where(function ($query) use ($userId) {
                $query->where('created_id', $userId)
                      ->orWhere('members', 'regex', new \MongoDB\BSON\Regex($userId));
            })
            ->get();

        if ($channels->isEmpty()) {
            $userInWorkspace = $workspaceChannels->contains(function ($channel) use ($userId) {
                return (string) $channel->created_id === $userId;
            });

            if (!$userInWorkspace) {
                return response()->forbidden('User not in this workspace.');
            }

            return response()->notFound('No channels found in this workspace.');
        }

        $request->merge(['channels' => $channels]);
        $request->attributes->set('channels', $channels);

        return $next($request);
    }
}