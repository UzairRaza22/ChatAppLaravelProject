<?php

namespace App\Http\Middleware\Message;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageExistsMiddleware
{
    /**
     * Verify the message exists (not soft-deleted) and belongs to the given channel.
     * Merges the resolved message into request attributes.
     *
     * Payload: message_id, channel_id
     */
    public function handle(Request $request, Closure $next): Response
    {

        $data = $request->all();
        $messageId = $data['message_id'] ?? null;
        $channelId = $data['channel_id'] ?? null;

        $message = Message::where('_id', $messageId)
            ->where('channel_id', $channelId)
            ->first();

        if (!$message) {
            return response()->notFound('Message not found.');
        }

        $request->attributes->set('message', $message);

        return $next($request);
    }
}
