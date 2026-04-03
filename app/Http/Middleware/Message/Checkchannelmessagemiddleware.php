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

        // Check if user is a member OR the creator of the channel
        $isCreator = (string) $channel->created_id === $userId || 
                    (is_object($channel->created_id) && (string) $channel->created_id === $userId);
        
        // Debug logging
        \Log::info('=== MESSAGE MIDDLEWARE DEBUG ===');
        \Log::info('Channel: ' . $channel->name);
        \Log::info('User ID: ' . $userId);
        \Log::info('Channel created_id: ' . $channel->created_id);
        \Log::info('Channel created_id type: ' . gettype($channel->created_id));
        \Log::info('Is creator: ' . ($isCreator ? 'YES' : 'NO'));
        \Log::info('Raw members: ' . json_encode($channel->members));
        
        $senderIsMember = $isCreator;
        
        // If not creator, check if user is member
        if (!$senderIsMember) {
            $members = $channel->members ?? [];
            
            // Multiple approaches to check membership
            foreach ($members as $member) {
                // Array with user_id field
                if (is_array($member) && isset($member['user_id'])) {
                    if ((string) $member['user_id'] === $userId) {
                        $senderIsMember = true;
                        break;
                    }
                }
                // Object with user_id property
                elseif (is_object($member) && property_exists($member, 'user_id')) {
                    if ((string) $member->user_id === $userId) {
                        $senderIsMember = true;
                        break;
                    }
                }
                // Simple string member
                elseif (is_string($member) && (string) $member === $userId) {
                    $senderIsMember = true;
                    break;
                }
            }
        }

        if (!$senderIsMember) {
            return response()->forbidden('You are not a member of this channel.');
        }

        // For direct channels — also verify the other member still belongs to the channel
        $isDirect = (string) $channel->type === 'direct';

        $otherMemberPresent = !$isDirect;
        
        // For direct channels, check if there's another member
        if ($isDirect) {
            $members = $channel->members ?? [];
            foreach ($members as $member) {
                $memberId = null;
                
                if (is_array($member) && isset($member['user_id'])) {
                    $memberId = (string) $member['user_id'];
                } elseif (is_object($member) && property_exists($member, 'user_id')) {
                    $memberId = (string) $member->user_id;
                } elseif (is_string($member)) {
                    $memberId = (string) $member;
                }
                
                if ($memberId && $memberId !== $userId) {
                    $otherMemberPresent = true;
                    break;
                }
            }
        }

        if (!$otherMemberPresent) {
            return response()->forbidden('The other user is no longer a member of this direct channel.');
        }

        $request->attributes->set('channel', $channel);

        return $next($request);
    }
}
