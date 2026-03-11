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
        $members = data_get($request, 'channel.members', []);

        $isCreator = collect($members)
            ->contains(fn ($member) => (string) data_get($member, 'user_id') === $userId && data_get($member, 'role') === 'creator');

        $requestedUserId = (string) $request->input('user_id', '');
        $isSelfRemovalRoute = $request->is('api/channels/remove-member');
        $isSelfMember = collect($members)
            ->contains(fn ($member) => (string) data_get($member, 'user_id') === $userId);

        if ($isSelfRemovalRoute && $requestedUserId !== '' && $requestedUserId === $userId && $isSelfMember) {
            return $next($request);
        }
        if (!$isCreator) {
            return response()->forbidden('Only creator can perform this action');
        }

        return $next($request);
    }
}