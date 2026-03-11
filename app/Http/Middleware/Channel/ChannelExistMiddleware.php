<?php

namespace App\Http\Middleware\Channel;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelExistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = (string) ($request->route('id') ?? $request->input('channel_id') ?? $request->query('channel_id'));
        $userId = (string) ($request->input('user_id') ?? $request->query('user_id'));

        if ($channelId !== '') {
            $channel = Channel::where('_id', $channelId)->first();

            if (!$channel) {
                return response()->notFound('Channel not found.');
            }

            data_set($request, 'channel', $channel);
            $request->attributes->set('channel', $channel);

            return $next($request);
        }

        if ($userId !== '') {
            $channels = Channel::where('members.user_id', $userId)->orWhere('created_id', $userId)->get();
            data_set($request, 'channels', $channels);
            $request->attributes->set('channels', $channels);

            return $next($request);
        }

        return response()->error('channel_id or user_id is required');
    }
}
