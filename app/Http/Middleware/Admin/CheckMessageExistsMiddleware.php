<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use App\Models\Message;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $messageId = $request->route('message_id');

        if ($messageId && !Message::find($messageId)) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found.'
            ], 404);
        }

        return $next($request);
    }
}
