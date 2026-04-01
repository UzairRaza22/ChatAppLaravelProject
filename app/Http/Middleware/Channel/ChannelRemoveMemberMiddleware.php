<?php

namespace App\Http\Middleware\Channel;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelRemoveMemberMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $channel = $request->attributes->get('channel');
        if (!$channel) {
            return response()->notFound('Channel not found.');
        }
        $userIds = $request->input('user_ids');
        $userIds = is_array($userIds) && count($userIds) ? $userIds : [(string) $request->input('user_id')];
        $userIds = collect($userIds)->map(fn ($id) => (string) $id)->filter()->values()->all();
        $request->merge(['user_ids' => $userIds]);

        $userId = (string) data_get($userIds, 0);
        $isDirect = (string) data_get($channel, 'type') === 'direct';
        
        if ($isDirect) {
            return response()->error('Cannot remove members from a direct channel');
        }

        $members = collect(data_get($channel, 'members', []))
            ->map(function ($member) {
                if (is_array($member)) {
                    return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '');
                }
                if (is_object($member)) {
                    return (string) (data_get($member, 'user_id') ?? data_get($member, '_id') ?? data_get($member, 'id'));
                }

                return (string) $member;
            })
            ->filter()
            ->values();

        $isMember = collect($userIds)->contains(fn ($id) => $members->contains((string) $id));

        if (!$isMember) {
            return response()->forbidden('User is not a member of this channel');
        }

        $request->merge([
            'members' => $members
                ->reject(fn ($member) => in_array((string) $member, $userIds, true))
                ->values()
                ->all()
        ]);

        return $next($request);
    }
}
