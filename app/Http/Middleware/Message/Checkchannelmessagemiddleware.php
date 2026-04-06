<?php

namespace App\Http\Middleware\Message;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckChannelMessageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = $request->input('channel_id');

        $channel = Channel::where(function ($query) use ($channelId) {
            $query->where('_id', $channelId)
                  ->orWhere('id', $channelId);
        })->first();

        if (!$channel) {
            return response()->notFound('Channel not found.');
        }

        $request->attributes->set('channel', $channel);

        $user   = $request->user();
        $userId = (string) $user->_id;

        // Check creator first
        $isCreator = (string) $channel->created_id === $userId;

        if (!$isCreator) {
            $isMember = $this->isChannelMember($channel->members, $userId);

            if (!$isMember) {
                return response()->forbidden('You are not a member of this channel.');
            }
        }

        // For direct channels — verify other member is present
        if ((string) $channel->type === 'direct') {
            $otherMemberPresent = $this->hasOtherMember($channel->members, $userId);

            if (!$otherMemberPresent) {
                return response()->forbidden('The other user is no longer a member of this direct channel.');
            }
        }

        return $next($request);
    }

    private function isChannelMember(array $members, string $userId): bool
    {
        foreach ($members as $member) {
            if ($this->extractMemberId($member) === $userId) {
                return true;
            }
        }
        return false;
    }

    private function hasOtherMember(array $members, string $userId): bool
    {
        foreach ($members as $member) {
            $memberId = $this->extractMemberId($member);
            if ($memberId && $memberId !== $userId) {
                return true;
            }
        }
        return false;
    }

    private function extractMemberId(mixed $member): ?string
    {
        if (is_array($member) && isset($member['user_id'])) {
            return (string) $member['user_id'];
        }
        if (is_object($member) && property_exists($member, 'user_id')) {
            return (string) $member->user_id;
        }
        if (is_string($member)) {
            return $member;
        }
        return null;
    }
}