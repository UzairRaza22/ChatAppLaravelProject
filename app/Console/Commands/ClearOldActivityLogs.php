<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;

class ClearOldActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove activity logs from MongoDB that are older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->comment('Cleaning up old activity logs...');

        // 30 days ago from today
        $cutoffDate = now()->subDays(30);

        // Perform the deletion
        $deletedCount = Activity::where('created_at', '<', $cutoffDate)->delete();

        if ($deletedCount > 0) {
            $this->info("Successfully cleared {$deletedCount} records older than {$cutoffDate->format('Y-m-d')}.");
        } else {
            $this->warn('No logs found older than 30 days.');
        }
    }
}