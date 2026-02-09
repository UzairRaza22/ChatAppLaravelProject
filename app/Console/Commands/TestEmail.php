<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Mail::raw('Test OTP: 123456', function ($message) {
                $message->to('test@example.com')->subject('Test Email from Laravel');
            });
            
            $this->info('Test email sent successfully to test@example.com');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            return 1;
        }
    }
}
