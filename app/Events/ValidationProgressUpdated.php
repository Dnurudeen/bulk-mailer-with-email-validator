<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ValidationProgressUpdated implements ShouldBroadcast
{
    public function __construct(
        public int $batchId,
        public array $stats, // ['valid'=>x,'invalid'=>y,'total'=>z,'status'=>..]
        public ?string $line = null
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel("validation.{$this->batchId}");
    }
    public function broadcastAs(): string
    {
        return 'progress';
    }
    public function broadcastWith(): array
    {
        return ['batchId' => $this->batchId, 'stats' => $this->stats, 'line' => $this->line];
    }
}