<?php

namespace App\Http\Middleware\Message;

use App\Http\Resources\MessageResource;
use App\Models\Channel;
use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReadMessagesMiddleware
{
    /**
     * Resolves paginated messages for both directchannel and channelmessage.
     *
     * - Validates channel_id exists and sender is a member
     * - Paginates to 20 messages per page
     * - Orders newest first (most recent at top in Postman)
     * - Merges resolved_messages into request attributes
     *
     * Payload: channel_id
     */
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = $request->input('channel_id');

        $channel = Channel::where('_id', $channelId)->first();

        if (!$channel) {
            return response()->notFound('Channel not found.');
        }

        $user   = $request->user();
        $userId = (string) $user->_id;

        $isMember = collect($channel->members ?? [])->contains(
            fn($m) => (string) ($m['user_id'] ?? '') === $userId
        );

        if (!$isMember) {
            return response()->forbidden('You are not a member of this channel.');
        }

        // Newest first → page 1 = most recent 20 messages
        $messages = Message::where('channel_id', (string) $channel->_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $request->attributes->set('channel', $channel);
        $request->attributes->set('resolved_messages', [
            'data'          => MessageResource::collection($messages->items()),
            'current_page'  => $messages->currentPage(),
            'per_page'      => $messages->perPage(),
            'total'         => $messages->total(),
            'last_page'     => $messages->lastPage(),
            'next_page_url' => $messages->nextPageUrl(),
            'prev_page_url' => $messages->previousPageUrl(),
        ]);

        return $next($request);
    }
}
