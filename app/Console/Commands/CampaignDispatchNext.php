<?php

namespace App\Console\Commands;

use App\Models\MailCampaign;
use Illuminate\Console\Command;

class CampaignDispatchNext extends Command
{
    protected $signature = 'campaign:dispatch-next {campaignId}';
    protected $description = 'Dispatch next batch for a campaign';

    public function handle()
    {
        $campaign = MailCampaign::findOrFail($this->argument('campaignId'));
        app(\App\Http\Controllers\CampaignController::class)->dispatchBatch($campaign);
        $this->info('Batch dispatched.');
        return self::SUCCESS;
    }
}
