<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageSenderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $message = $request->attributes->get('message');
        $user   = $request->user();
        
        // Validate user is sender of this message
        if ((string) $message->sender_id !== (string) $user->_id) {
            return response()->forbidden('Only the sender can perform this action.');
        }
        
        // Additional validation for update/delete operations
        $requestMethod = $request->method();
        if (in_array($requestMethod, ['PATCH', 'PUT', 'DELETE'])) {
            if ((string) $message->sender_id !== (string) $user->_id) {
                return response()->forbidden('Only the sender can update or delete this message.');
            }
        }
        
        return $next($request);
    }
}
