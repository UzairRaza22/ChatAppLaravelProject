<?php

namespace App\Http\Middleware\Message;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckChannelInWorkspaceMiddleware
{
    /**
     * For Channel Messages: ensure the given channel belongs to the workspace.
     * Must run AFTER CheckWorkspaceMemberMiddleware so 'workspace' is in request.
     * Only runs when 'channel_id' is present in the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = $request->input('channel_id');

        // Not a channel message — skip
        if (!$channelId) {
            return $next($request);
        }

        $workspace = data_get($request, 'workspace');

        $channel = Channel::where('_id', $channelId)
            ->where('workspace_id', $workspace->_id)
            ->first();

        if (!$channel) {
            return response()->json([
                'message' => 'Channel not found in this workspace.'
            ], 404);
        }

        $request->merge(['channel' => $channel]);

        return $next($request);
    }
}
