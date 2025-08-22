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

    public int $campaignId;
    public array $stats;
    public string $line;

    public function __construct(int $campaignId, array $stats, string $line)
    {
        $this->campaignId = $campaignId;
        $this->stats = $stats;
        $this->line  = $line;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("campaign.{$this->campaignId}");
    }

    public function broadcastAs(): string
    {
        return 'progress'; // 👈 matches .listen('.progress')
    }
}
