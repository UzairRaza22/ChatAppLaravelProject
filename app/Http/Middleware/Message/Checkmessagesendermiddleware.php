<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageSenderMiddleware
{
    /**
     * Ensure the authenticated user is the original sender of the message.
     * Must run AFTER CheckMessageExistsMiddleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user    = $request->user();
        $message = data_get($request, 'message');

        if (!$message) {
            return response()->json([
                'message' => 'Message not found.'
            ], 404);
        }

        if ((string) $message->sender_id !== (string) $user->_id) {
            return response()->json([
                'message' => 'Unauthorized. Only the sender can perform this action.'
            ], 403);
        }

        return $next($request);
    }
}
