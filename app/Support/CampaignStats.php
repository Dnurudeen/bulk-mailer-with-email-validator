<?php

namespace App\Support;

use App\Models\MailCampaign;
use Illuminate\Support\Facades\DB;

class CampaignStats
{
    public static function snapshot(MailCampaign $c): array
    {
        $counts = DB::table('campaign_recipient')
            ->where('mail_campaign_id', $c->id)
            ->selectRaw("
        SUM(CASE WHEN campaign_recipient.status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN campaign_recipient.status='queued'  THEN 1 ELSE 0 END) as queued,
        SUM(CASE WHEN campaign_recipient.status='sent'    THEN 1 ELSE 0 END) as sent,
        SUM(CASE WHEN campaign_recipient.status='failed'  THEN 1 ELSE 0 END) as failed
    ")->first();

        $pending = (int)$counts->pending;
        $queued  = (int)$counts->queued;
        $sent    = (int)$counts->sent;
        $failed  = (int)$counts->failed;
        $total   = max(1, $pending + $queued + $sent + $failed);
        $percent = round((($sent + $failed) / $total) * 100, 1);

        return [
            'pending' => $pending,
            'queued'  => $queued,
            'sent'    => $sent,
            'failed'  => $failed,
            'status'  => $c->status,
            'percent' => $percent,
            'total'   => $total,
        ];
    }
}
