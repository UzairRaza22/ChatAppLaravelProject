<?php

namespace App\Http\Middleware\Channel;

use Closure;
use App\Models\Channel;

class ChannelExistMiddleware
{
    public function handle($request, Closure $next)
    {
        // MongoDB uses _id field
        $channelId = $request->route('id');

        $channel = Channel::where('_id', $channelId)->first();

        if (!$channel) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        // Attach the channel to the request for controllers
        $request->channel = $channel;

        return $next($request);
    }
}
