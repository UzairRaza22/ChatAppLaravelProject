<?php

namespace App\Http\Middleware\Message;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageReactionMiddleware
{
    /**
     * Validate emoji reaction request and resolve the target message.
     *
     * Accepts message_ids as an array (uses first element) — consistent with
     * how read-by payloads are sent from the client.
     *
     * Requires:
     *   - message.channel.check (resolves 'channel' attribute, validates membership)
     *
     * Payload:
     *   channel_id  – string (required)
     *   message_ids – array of strings (required, min 1); first element is used
     *   emoji       – string (required, trimmed, max 8 chars)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->validate([
            'channel_id'    => 'required|string',
            'message_ids'   => 'required|array|min:1',
            'message_ids.*' => 'required|string',
            'emoji'         => 'required|string|max:8',
        ]);

        $channel = $request->attributes->get('channel');

        if (!$channel) {
            return response()->forbidden('Channel not resolved. Ensure message.channel.check runs first.');
        }

        // Use the first message_id from the array
        $messageId = (string) data_get($request->input('message_ids'), 0);
        $channelId = (string) data_get($channel, '_id');

        $message = Message::where('_id', $messageId)
            ->where('channel_id', $channelId)
            ->first();

        if (!$message) {
            return response()->notFound('Message not found.');
        }

        $emoji = trim($request->input('emoji'));

        if (empty($emoji)) {
            return response()->error('Emoji cannot be empty.', 422);
        }

        $request->attributes->set('message', $message);
        $request->attributes->set('resolved_emoji', $emoji);

        return $next($request);
    }
}
