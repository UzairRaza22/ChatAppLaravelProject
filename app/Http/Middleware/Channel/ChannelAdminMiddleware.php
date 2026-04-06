<?php

namespace App\Http\Middleware\Channel;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user   = $request->user() ?? $request->input('verified_user');
        $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id') ?? auth()->id());

        $channel = $request->attributes->get('channel');
        $members = data_get($request, 'channel.members', []);

        // Check 1: user is the channel creator (stored in created_id field)
        $isCreator = $channel && (string) $channel->created_id === $userId;

        // Check 2: user has role 'admin' or 'creator' in members array
        if (!$isCreator) {
            $isCreator = collect($members)->contains(function ($member) use ($userId) {
                $memberId = (string) data_get($member, 'user_id', '');
                $role     = data_get($member, 'role', '');
                return $memberId === $userId && in_array($role, ['admin', 'creator'], true);
            });
        }

        // Self-removal exception — member can remove themselves
        $requestedUserId  = (string) $request->input('user_id', '');
        $isSelfRemovalRoute = $request->is('api/channels/remove-member');
        $isSelfMember = collect($members)->contains(
            fn($member) => (string) data_get($member, 'user_id') === $userId
        );

        if ($isSelfRemovalRoute && $requestedUserId !== '' && $requestedUserId === $userId && $isSelfMember) {
            return $next($request);
        }

        if (!$isCreator) {
            return response()->forbidden('Only the channel admin can perform this action.');
        }

        return $next($request);
    }
}