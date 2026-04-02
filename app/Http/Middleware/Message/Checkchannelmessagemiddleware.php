<?php

namespace App\Http\Middleware\Message;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckChannelMessageMiddleware
{
    /**
     * Unified middleware for both directchannel and channelmessage.
     *
     * For directchannel:
     *   - channel.type === 'direct'
     *   - sender must be a member of the direct channel
     *   - the other user in the channel must also be a member
     *
     * For channelmessage:
     *   - channel.type === 'public' or 'private'
     *   - sender must be a member of the channel
     *
     * Token is read from Authorization header (set by check.token middleware).
     * Payload: channel_id
     */
    public function handle(Request $request, Closure $next): Response
    {
        $channelId = $request->input('channel_id');

        $channel = Channel::where('_id', $channelId)->first();

        if (!$channel) {
            return response()->notFound('Channel not found.');
        }

        $user   = $request->user();
        $userId = (string) $user->_id;

        $members = collect($channel->members ?? []);

        // Check if user is a member OR the creator of the channel
        $isCreator = (string) $channel->created_id === $userId;
        
        $senderIsMember = $isCreator || $members->contains(function ($member) use ($userId) {
            // Handle the specific member structure: {"user_id": "...", "role": "..."}
            if (is_array($member) && isset($member['user_id'])) {
                return (string) $member['user_id'] === $userId;
            }
            if (is_object($member) && isset($member->user_id)) {
                return (string) $member->user_id === $userId;
            }
            // Fallback for simple string members
            if (is_string($member)) {
                return (string) $member === $userId;
            }
            return false;
        });

        if (!$senderIsMember) {
            return response()->forbidden('You are not a member of this channel.');
        }

        // For direct channels — also verify the other member still belongs to the channel
        $isDirect = (string) $channel->type === 'direct';

        $otherMemberPresent = !$isDirect || $members->contains(function ($member) use ($userId) {
            // Handle the specific member structure for other member check
            if (is_array($member) && isset($member['user_id'])) {
                return (string) $member['user_id'] !== $userId;
            }
            if (is_object($member) && isset($member->user_id)) {
                return (string) $member->user_id !== $userId;
            }
            // Fallback for simple string members
            if (is_string($member)) {
                return (string) $member !== $userId;
            }
            return false;
        });

        if (!$otherMemberPresent) {
            return response()->forbidden('The other user is no longer a member of this direct channel.');
        }

        $request->attributes->set('channel', $channel);

        return $next($request);
    }
}
