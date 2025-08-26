<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('campaign.{campaignId}', function ($user, $campaignId) {
    $campaign = \App\Models\MailCampaign::find($campaignId);
    return $campaign && $campaign->user_id === $user->id;
});

Broadcast::channel('validation.{batchId}', function ($user, $batchId) {
    $batch = \App\Models\EmailValidationBatch::find($batchId);
    return $batch && $batch->user_id === $user->id; // adjust ownership check
});

// Broadcast::channel('validation.{batchId}', function ($user, $batchId) {
//     return \App\Models\EmailValidationBatch::where('id', $batchId)->where('user_id', $user->id);
// });
