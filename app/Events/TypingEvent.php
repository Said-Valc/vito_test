<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $user_id;
    public $recipient_id;

    public function __construct($user_id, $recipient_id)
    {
        $this->user_id = $user_id;
        $this->recipient_id = $recipient_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->recipient_id);
    }

    public function broadcastAs()
    {
        return 'Typing';
    }

    public function broadcastWith()
    {
        return ['user_id' => $this->user_id];
    }
}
