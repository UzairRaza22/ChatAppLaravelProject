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
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = $request->input('channel_id');

        $channel = Channel::where('_id', $channelId)->first();

        if (!$channel) {
            return response()->notFound('Channel not found.');
        }

        $user   = $request->user();
        $userId = (string) $user->_id;

        // Check creator first — defined BEFORE use
        $isCreator = (string) $channel->created_id === $userId;

        if (!$isCreator) {
            $isMember = false;
            foreach ($channel->members as $member) {
                $memberId = null;

                if (is_array($member) && isset($member['user_id'])) {
                    $memberId = (string) $member['user_id'];
                } elseif (is_object($member) && property_exists($member, 'user_id')) {
                    $memberId = (string) $member->user_id;
                } elseif (is_string($member)) {
                    $memberId = $member;
                }

                if ($memberId === $userId) {
                    $isMember = true;
                    break;
                }
            }

            if (!$isMember) {
                return response()->forbidden('You are not a member of this channel.');
            }
        }

        $cursor = $request->input('cursor');
        $limit = $request->input('limit', 20);

        $query = Message::where('channel_id', (string) $channel->_id)
            ->orderBy('created_at', 'desc');

        $query->where(function ($query) {
            $query->whereNull('status')
                ->orWhere('status', 'sent');
        });

        if ($cursor) {
            $query->where('_id', '<', $cursor);
        }

        $messages = $query->limit($limit)->get();

        $hasMore = $messages->count() === $limit;
        $nextCursor = $hasMore ? (string) $messages->last()->_id : null;

        $request->attributes->set('channel', $channel);
        $request->attributes->set('resolved_messages', [
            'data'       => MessageResource::collection($messages),
            'has_more'   => $hasMore,
            'next_cursor' => $nextCursor,
            'limit'      => $limit,
        ]);

        return $next($request);
    }
}
