<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckChannelOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $channelId = $request->route('channelId') ?? $request->route('id');
        
        if (!$channelId) {
            abort(422, 'Channel ID is required');
        }

        $user = $request->user();
        
        // Check channel ownership (created_by field)
        $isOwner = $user->channels()
            ->where('_id', $channelId)
            ->where('created_by', $user->id)
            ->exists();

        if (!$isOwner) {
            abort(403, 'Only channel creator can perform this action');
        }

        return $next($request);
    }
}
