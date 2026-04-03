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

        if ($channelId !== '') {
            // Get specific channel
            $channel = Channel::where('_id', $channelId)->first();

            if (!$channel) {
                return response()->notFound('Channel not found.');
            }

            $request->merge(['channel' => $channel]);
            $request->attributes->set('channel', $channel);

            return $next($request);
        }

        // For listing channels - get ALL channels (same as teams logic)
        $channels = Channel::all();

        if ($channels->isEmpty()) {
            abort(404, 'No channels found.');
        }

        $request->merge(['channels' => $channels]);
        $request->attributes->set('channels', $channels);

        return $next($request);
    }
}