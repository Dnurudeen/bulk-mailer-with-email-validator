<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $campaignId;
    public $stats;
    public $line;

    public function __construct($campaignId, $stats, $line)
    {
        $this->campaignId = $campaignId;
        $this->stats = $stats;
        $this->line  = $line;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("campaign.{$this->campaignId}");
    }

    public function broadcastAs()
    {
        return 'progress'; // 👈 matches .listen('.progress')
    }

    public function broadcastWith(): array
    {
        return [
            'campaignId' => $this->campaignId,
            'stats'      => $this->stats,
            'line'       => $this->line,
        ];
    }
}
