<?php

namespace App\Console;

use App\Models\MailCampaign;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Every 2 hours, pick runnable campaigns & queue next batch
        $schedule->call(function () {
            MailCampaign::runnable()
                ->orderBy('id')
                ->each(function ($campaign) {
                    // set to sending if scheduled
                    if ($campaign->status === 'scheduled') {
                        $campaign->update(['status' => 'sending']);
                    }

                    // Dispatch a batch
                    app(\App\Http\Controllers\CampaignController::class)
                        ->dispatchBatch($campaign);
                });
        })->everyTwoHours()->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
