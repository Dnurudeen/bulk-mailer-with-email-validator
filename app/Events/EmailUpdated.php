<?php

namespace App\Events;

use App\Models\Email;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class EmailUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $email;
    public $counts;

    public function __construct(Email $email)
    {
        $this->email = $email;

        // ✅ Live summary counts
        $this->counts = [
            'valid' => Email::where('status', 'valid')->count(),
            'invalid' => Email::where('status', 'invalid')->count(),
            'unknown' => Email::where('status', 'unknown')->count(),
        ];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('emails');
    }

    public function broadcastAs(): string
    {
        return 'EmailUpdated';
    }
}
