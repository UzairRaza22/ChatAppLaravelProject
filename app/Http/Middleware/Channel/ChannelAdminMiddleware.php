<?php

namespace App\Http\Middleware\Channel;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? $request->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());
        $members = $request->channel->members ?? [];

        $isCreator = collect($members)
            ->contains(fn ($member) => (string) data_get($member, 'user_id') === $userId && data_get($member, 'role') === 'creator');

        if (!$isCreator) {
            return response()->json(['error' => 'Only creator can perform this action'], 403);
        }

        return $next($request);
    }
}