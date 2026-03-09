<?php

namespace App\Http\Middleware\Channel;

use Closure;
use App\Models\Channel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelExistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = $request->route('id');

        $channel = Channel::where('_id', $channelId)->first();

        if (!$channel) {
            return response()->notFound('Channel not found.');
        }

        // Attach the channel to the request for controllers
        $request->channel = $channel;
        $request->attributes->set('channel', $channel);

        return $next($request);
    }
}
