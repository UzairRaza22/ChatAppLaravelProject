<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class ProcessScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send scheduled messages that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now('UTC');
        $processed = 0;
        $skipped = 0;

        Message::where(function ($query) use ($now) {
            $query->where('status', 'scheduled')
                ->where('schedule_time', '<=', $now);
        })->orWhere(function ($query) use ($now) {
            $query->where('status', 'processing')
                ->where('updated_at', '<=', $now->copy()->subMinutes(5));
        })
            ->orderBy('schedule_time', 'asc')
            ->chunk(100, function ($messages) use (&$processed, &$skipped) {
                foreach ($messages as $message) {
                    $claimed = Message::where('_id', (string) $message->_id)
                        ->where(function ($query) {
                            $query->where('status', 'scheduled')
                                ->orWhere('status', 'processing');
                        })
                        ->update(['status' => 'processing']);

                    if (!$claimed) {
                        $skipped++;
                        continue;
                    }

                    try {
                        // Existing send behavior is tied to the message record itself.
                        Message::where('_id', (string) $message->_id)
                            ->update(['status' => 'sent']);

                        $processed++;
                    } catch (\Throwable $e) {
                        Message::where('_id', (string) $message->_id)
                            ->update(['status' => 'scheduled']);

                        $this->error('Failed to process scheduled message: ' . $e->getMessage());
                    }
                }
            });

        $this->info('Processed scheduled messages: ' . $processed);

        if ($skipped > 0) {
            $this->info('Skipped (already processing): ' . $skipped);
        }

        return Command::SUCCESS;
    }
}
