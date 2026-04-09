<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\BSON\ObjectId;

class SendScheduledMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $messageId;

    public function __construct(string $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(): void
    {
        $id = new ObjectId($this->messageId);

        $message = Message::where('_id', $id)->first();

        if (!$message || $message->status === 'sent') {
            return;
        }

        $message->update(['status' => 'sent']);
    }
}
