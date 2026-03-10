<?php

namespace App\Http\Middleware\Message;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckChannelInWorkspaceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = $request->input('channel_id');

        // Not a channel message — skip
        if (!$channelId) {
            return $next($request);
        }

        $sender  = $request->user();
        $channel = Channel::where('_id', $channelId)->first();

        if (!$channel) {
            return response()->notFound('Channel not found.');
        }

        $isMember = $channel->members()->get()->contains('_id', $sender->_id);

        if (!$isMember) {
            return response()->forbidden('You are not a member of this channel.');
        }

        $request->merge([
            'channel'   => $channel,
            'workspace' => $channel->workspace,
        ]);

        return $next($request);
    }
}
