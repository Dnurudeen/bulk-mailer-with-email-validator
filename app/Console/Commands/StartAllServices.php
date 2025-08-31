<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartAllServices extends Command
{
    protected $signature = 'app:start';
    protected $description = 'Start serve, queue, reverb, and schedule in one command';

    public function handle()
    {
        $this->info("Starting Laravel services...");

        $commands = [
            'serve',
            'queue:work --tries=3',
            'reverb:start',
            'schedule:work',
        ];

        foreach ($commands as $cmd) {
            $this->info("Running: php artisan $cmd");
            exec("php artisan $cmd > /dev/null 2>&1 &");
        }

        $this->info("✅ All services started successfully!");
        return Command::SUCCESS;
    }
}
