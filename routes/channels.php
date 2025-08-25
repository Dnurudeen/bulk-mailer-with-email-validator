<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('campaign.{campaignId}', function ($user, $campaignId) {
//     return (int) $user->id === \App\Models\MailCampaign::find($campaignId)?->user_id;
// });

Broadcast::channel('campaign.{campaignId}', function ($user, $campaignId) {
    $campaign = \App\Models\MailCampaign::find($campaignId);
    return $campaign && $campaign->user_id === $user->id;
});

// Broadcast::channel('campaign.{campaignId}', function ($user, $campaignId) {
//     return true; // ✅ allow any authenticated user to listen
// });