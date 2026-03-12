<?php

namespace App\Http\Middleware\Message;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReadByMiddleware
{
    /**
     * Mark a batch of messages as read by the authenticated user.
     *
     * Requires message.channel.check to have run first (resolves 'channel' attribute).
     *
     * Payload:
     *   channel_id  – string (required)
     *   message_ids – array of strings (required, min 1)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->validate([
            'channel_id'    => 'required|string',
            'message_ids'   => 'required|array|min:1',
            'message_ids.*' => 'required|string',
        ]);

        // Channel is already resolved + membership verified by message.channel.check
        $channel = $request->attributes->get('channel');

        if (!$channel) {
            return response()->forbidden('Channel not resolved. Ensure message.channel.check runs first.');
        }

        $user       = $request->user();
        $userId     = (string) data_get($user, '_id');
        $channelId  = (string) data_get($channel, '_id');
        $messageIds = $request->input('message_ids');

        $count = Message::markReadBy($channelId, $messageIds, $userId);

        $request->attributes->set('resolved_read_count', $count);

        return $next($request);
    }
}
