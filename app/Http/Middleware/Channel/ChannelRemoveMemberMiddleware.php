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
        $userId = (string) $request->input('user_id');
        $isDirect = (string) data_get($channel, 'type') === 'direct';
        
        if ($isDirect) {
            return response()->error('Cannot remove members from a direct channel');
        }

        $members = collect(data_get($channel, 'members', []));
        $isMember = $members->contains(
            fn ($member) => (string) data_get($member, 'user_id') === (string) $userId
        );

        if (!$isMember) {
            return response()->forbidden('User is not a member of this channel');
        }

        $request->merge([
            'members' => $members
                ->reject(fn ($member) => (string) data_get($member, 'user_id') === (string) $userId)
                ->values()
                ->all()
        ]);

        return $next($request);
    }
}
