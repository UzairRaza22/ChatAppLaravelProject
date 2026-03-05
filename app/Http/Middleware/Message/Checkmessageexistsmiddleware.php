<?php

namespace App\Http\Middleware\Message;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageExistsMiddleware
{
    /**
     * Verify the message exists (not soft-deleted).
     * Merges the resolved message into the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $messageId = $request->input('message_id');

        $message = Message::where('_id', $messageId)->first();

        if (!$message) {
            return response()->json([
                'message' => 'Message not found.'
            ], 404);
        }

        $request->merge(['message' => $message]);

        return $next($request);
    }
}
