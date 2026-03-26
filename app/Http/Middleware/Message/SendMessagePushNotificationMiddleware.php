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
                $preview = $request->input('message')
                    ? substr($request->input('message'), 0, 100)
                    : 'Sent a file';

                try {
                    SendMessagePushNotificationJob::dispatch(
                        (string) $request->input('receiver_id'),
                        'New message',
                        $preview,
                        [
                            'type'       => 'message',
                            'message_id' => (string) $message['id'],
                            'sender_id'  => (string) $user->_id,
                        ]
                    );
                } catch (\Throwable $e) {
                    // FCM / push notification failure must never break the API response.
                    // Log for debugging but return the successful message response regardless.
                    logger()->error('Push notification dispatch failed: ' . $e->getMessage(), [
                        'receiver_id' => $request->input('receiver_id'),
                        'message_id'  => $message['id'] ?? null,
                    ]);
                }
            }
        }

        return $response;
    }
}
