<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('campaign.{campaignId}', function ($user, $campaignId) {
    return (int) $user->id === \App\Models\MailCampaign::find($campaignId)?->user_id;
});
