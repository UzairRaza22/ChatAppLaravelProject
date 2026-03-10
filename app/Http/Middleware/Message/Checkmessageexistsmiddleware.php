<?php

namespace App\Http\Middleware\Message;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $message = Message::where('_id', $request->input('message_id'))->first();

        if (!$message) {
            return response()->notFound('Message not found.');
        }

        $request->merge(['message' => $message]);

        return $next($request);
    }
}
