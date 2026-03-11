<?php

namespace App\Http\Middleware\Message;

use App\Models\Message;
use App\Http\Resources\MessageResource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReadMessagesMiddleware
{
    /**
     * Resolves messages for DM or Channel and merges into request.
     * Controller then just returns the resolved data — no if/else needed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user      = $request->user();
        $workspace = data_get($request, 'workspace');
        $receiver  = data_get($request, 'receiver');
        $channel   = data_get($request, 'channel');

        $messages = $receiver
            // ── Direct Messages ───────────────────────────────────────────
            ? Message::where('workspace_id', $workspace->_id)
            ->where(function ($query) use ($user, $receiver) {
                $query->where(function ($q) use ($user, $receiver) {
                    $q->where('sender_id', $user->_id)
                        ->where('receiver_id', $receiver->_id);
                })->orWhere(function ($q) use ($user, $receiver) {
                    $q->where('sender_id', $receiver->_id)
                        ->where('receiver_id', $user->_id);
                });
            })
            ->whereNull('channel_id')
            ->orderBy('created_at', 'desc')
            ->get()
            // ── Channel Messages ──────────────────────────────────────────
            : Message::where('channel_id', $channel->_id)
            ->whereNull('receiver_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $request->merge(['resolved_messages' => $messages]);

        return $next($request);
    }
}
