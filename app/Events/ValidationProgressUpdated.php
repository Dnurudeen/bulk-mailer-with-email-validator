<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ValidationProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $batchId;
    public array $stats;
    public string $line;
    public ?array $result; // optional single result update

    public function __construct(int $batchId, array $stats, string $line, ?array $result = null)
    {
        $this->batchId = $batchId;
        $this->stats   = $stats;
        $this->line    = $line;
        $this->result  = $result;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("validation.{$this->batchId}");
    }

    public function broadcastAs(): string
    {
        return 'progress';
    }

    public function broadcastWith(): array
    {
        return [
            'batchId' => $this->batchId,
            'stats'   => $this->stats,
            'line'    => $this->line,
            'result'  => $this->result,
        ];
    }
}
