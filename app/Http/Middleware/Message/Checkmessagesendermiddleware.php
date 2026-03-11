<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageSenderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user    = $request->user();
        $message = $request->attributes->get('message');

        if (!$message) {
            return response()->notFound('Message not found.');
        }

        if ((string) $message->sender_id !== (string) $user->_id) {
            return response()->forbidden('Only the sender can perform this action.');
        }

        return $next($request);
    }
}
