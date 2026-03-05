<?php

namespace App\Http\Middleware\Channel;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $channel = $request->channel;

        $isAdmin = collect($channel->members ?? [])->contains(function ($member) use ($user) {
            return (string) ($member['user_id'] ?? '') === (string) ($user?->_id ?? '') &&
                ($member['role'] ?? null) === 'admin';
        });

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Only channel admin can perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
