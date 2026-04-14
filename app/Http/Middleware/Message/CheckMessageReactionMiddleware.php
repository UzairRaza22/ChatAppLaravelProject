<?php

namespace App\Http\Middleware\Message;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageReactionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->validate([
            'channel_id'    => 'required|string',
            'message_ids'   => 'required|array|min:1',
            'message_ids.*' => 'required|string',
            'emoji'         => ['required', 'string', Rule::in(['👍', '❤️', '😂', '😮', '😢', '🔥'])],
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
        $userId = (string) auth()->id();

        // Detect user's current reaction across all emojis
        $reactions = $message->reactions ?? [];
        $userCurrentReaction = null;

        foreach ($reactions as $existingEmoji => $userIds) {
            if (in_array($userId, (array) $userIds)) {
                $userCurrentReaction = $existingEmoji;
                break;
            }
        }

        $request->attributes->set('message', $message);
        $request->attributes->set('resolved_emoji', $emoji);
        $request->attributes->set('user_current_reaction', $userCurrentReaction); // null if no reaction
        $request->attributes->set('is_double_click', $userCurrentReaction === $emoji); // true if same emoji

        return $next($request);
    }
}
