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
        $isAdmin = collect($members)
            ->contains(fn ($member) => (string) data_get($member, 'user_id') === $userId && data_get($member, 'role') === 'admin');

        if (!$isAdmin) {
            return response()->forbidden('Only admin can perform this action.');
        }
        return $next($request);
    }
}
