<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Jobs\SendMessagePushNotificationJob;

class SendMessagePushNotificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if message was successfully created (status 201)
        if ($response->getStatusCode() === 201 && $request->input('receiver_id')) {
            $responseData = json_decode($response->getContent(), true);
            $message = data_get($responseData, 'data.message');
            $user = $request->user();

            if ($message) {
                $preview = $request->input('content')
                    ? substr($request->input('content'), 0, 100)
                    : 'Sent a file';

                SendMessagePushNotificationJob::dispatch(
                    (string) $request->input('receiver_id'),
                    'New message',
                    $preview,
                    [
                        'type' => 'message',
                        'message_id' => (string)$message['id'],
                        'sender_id' => (string)$user->_id
                    ]
                );
            }
        }

        return $response;
    }
}
