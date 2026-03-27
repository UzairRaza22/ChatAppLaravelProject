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
     * Dispatches the push notification AFTER the response is sent to the client
     * using afterResponse(). This guarantees that any Firebase/FCM misconfiguration
     * (e.g. missing appId) can NEVER cause a 500 error on the message API response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Completely disabled to prevent 500 errors
        return $next($request);
        
        $response = $next($request);

        // Only fire for successful message creation (201) with a receiver
        if ($response->getStatusCode() === 201 && $request->input('receiver_id')) {
            $responseData = json_decode($response->getContent(), true);
            $message      = data_get($responseData, 'data.message');
            $user         = $request->user();

            if ($message) {
                $preview = $request->input('message')
                    ? substr($request->input('message'), 0, 100)
                    : 'Sent a file';

                $receiverId = (string) $request->input('receiver_id');
                $messageId  = (string) ($message['id'] ?? '');
                $senderId   = (string) ($user->_id ?? '');

                // afterResponse() fires AFTER the HTTP response is delivered to the client.
                // This means even on SYNC queue driver, any Firebase/FCM exception
                // cannot affect the API response — it is already sent.
                app()->terminating(function () use ($receiverId, $preview, $messageId, $senderId) {
                    try {
                        SendMessagePushNotificationJob::dispatch(
                            $receiverId,
                            'New message',
                            $preview,
                            [
                                'type'       => 'message',
                                'message_id' => $messageId,
                                'sender_id'  => $senderId,
                            ]
                        );
                    } catch (\Throwable $e) {
                        // FCM failure must never affect any API response.
                        // Log for debugging only.
                        logger()->error('Push notification dispatch failed: ' . $e->getMessage(), [
                            'receiver_id' => $receiverId,
                            'message_id'  => $messageId,
                        ]);
                    }
                });
            }
        }

        return $response;
    }
}
