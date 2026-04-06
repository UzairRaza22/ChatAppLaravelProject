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