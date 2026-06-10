<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'listable']);
        
        // Добавляем listing_type для фронтенда
        if ($this->message->listable) {
            $typeMapping = [
                'App\\Models\\AutoListing' => 'auto',
                'App\\Models\\NedvizhimostListing' => 'nedvizhimost',
                'App\\Models\\ElektronikaListing' => 'elektronika',
                'App\\Models\\HobbyListing' => 'hobby',
                'App\\Models\\RabotaVacancy' => 'vacancy',
                'App\\Models\\RabotaResume' => 'resume',
                'App\\Models\\UslugiListing' => 'uslugi',
                'App\\Models\\LichnieVeschiListing' => 'lichnie_veschi',
                'App\\Models\\DlyaDomaListing' => 'dlya_doma',
            ];
            
            $class = get_class($this->message->listable);
            $this->message->listable->listing_type = $typeMapping[$class] ?? null;
        }
    }

    public function broadcastOn()
    {
        // Отправляем в канал получателя И отправителя (чтобы оба видели обновление)
        return [
            new PrivateChannel('chat.' . $this->message->receiver_id),
            new PrivateChannel('chat.' . $this->message->sender_id)
        ];
    }
    
    public function broadcastAs()
    {
        return 'MessageSent';
    }
    
    public function broadcastWith()
    {
        return [
            'message' => $this->message->toArray(),
        ];
    }
}