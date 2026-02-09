<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Channel;
use App\Models\Workspace;

class CheckChannelAccess
{
    public function handle(Request $request, Closure $next)
    {
        $channelId = $request->route('channelId') ?? $request->route('id');
        
        if (!$channelId) {
            abort(422, 'Channel ID is required');
        }

        $user = $request->user();
        
        // Check channel access
        $hasAccess = $user->channels()->where('_id', $channelId)->exists();

        if (!$hasAccess) {
            abort(403, 'Access denied to this channel');
        }

        return $next($request);
    }
}
