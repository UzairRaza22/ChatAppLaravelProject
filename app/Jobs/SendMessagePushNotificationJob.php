<?php

namespace App\Jobs;

use App\Models\FcmToken;
use App\Services\Push\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessagePushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 5;

    protected $receiverId;
    protected $title;
    protected $body;
    protected $data;

    public function __construct($receiverId, $title, $body, $data = [])
    {
        $this->receiverId = $receiverId;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function handle(FcmService $fcmService)
    {
        $tokens = FcmToken::where('user_id', $this->receiverId)->get();

        foreach ($tokens as $tokenRecord) {
            try {
                $fcmService->sendToToken(
                    $tokenRecord->token,
                    $this->title,
                    $this->body,
                    $this->data
                );
            } catch (\Exception $e) {
                if ($e->getMessage() === 'INVALID_TOKEN' || strpos($e->getMessage(), 'not a valid FCM registration token') !== false) {
                    $tokenRecord->delete();
                    Log::info("FCM Token deleted for user {$this->receiverId} due to invalid/unregistered status.");
                } else {
                    Log::error("Failed to send push notification to {$this->receiverId}: " . $e->getMessage());
                    // Not throwing here to allow other tokens to process. Failed jobs handle full failures.
                }
            }
        }
    }
}
