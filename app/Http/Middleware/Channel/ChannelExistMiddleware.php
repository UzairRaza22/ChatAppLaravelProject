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
        $channelId = (string) $request->route('id');

        $channel = Channel::where('_id', $channelId)
            ->orWhere('id', $channelId)
            ->first();

        if (!$channel) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        $request->channel = $channel;
        $request->attributes->set('channel', $channel);

        return $next($request);
    }
}