<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class IndexMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:index-messages {--flush : Flush existing index first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all messages in Algolia for search functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('flush')) {
            $this->info('Flushing existing messages index...');
            Message::removeFromSearch();
            $this->info('Index flushed successfully.');
        }

        $this->info('Starting to index messages in Algolia...');

        $bar = $this->output->createProgressBar(Message::count());
        $bar->start();

        Message::with(['sender', 'channel'])
            ->chunk(100, function ($messages) use ($bar) {
                foreach ($messages as $message) {
                    $message->searchable();
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('All messages have been indexed successfully!');
        
        $this->info('Total messages indexed: ' . Message::count());
        
        return Command::SUCCESS;
    }
}
