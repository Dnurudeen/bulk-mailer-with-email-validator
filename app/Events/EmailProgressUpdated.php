<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EmailProgressUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public string $status;
    public int $index;
    public int $total;
    public string $email;
    public ?string $error;

    public function __construct(string $status, int $index, int $total, string $email, ?string $error = null)
    {
        $this->status = $status;
        $this->index  = $index;
        $this->total  = $total;
        $this->email  = $email;
        $this->error  = $error;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('campaign-progress');
    }

    public function broadcastAs(): string
    {
        return 'progress.updated';
    }
}
