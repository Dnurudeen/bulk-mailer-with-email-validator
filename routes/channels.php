<?php

use App\Models\ValidationBatch;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('campaign.{campaignId}', function ($user, $campaignId) {
    $campaign = \App\Models\MailCampaign::find($campaignId);
    return $campaign && $campaign->user_id === $user->id;
});

Broadcast::channel('validation.{batchId}', function ($user, $batchId) {
    $batch = ValidationBatch::find($batchId);
    return $batch && $batch->user_id === $user->id;
});