<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $listingTitle = $this->order->listable->title ?? 'Объявление';
        $amount = $this->order->amount ?? $this->order->listable->price ?? 0;
        $formattedAmount = number_format($amount, 0, ',', ' ') . ' ₽';

        return (new MailMessage)
            ->subject('🛒 Новая сделка на ' . config('app.name'))
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line($this->order->buyer->name . ' хочет купить ваш товар:')
            ->line('**' . $listingTitle . '**')
            ->line('Сумма сделки: **' . $formattedAmount . '**')
            ->action('Перейти к сделке', route('orders.index'))
            ->line('Вы можете принять сделку или отклонить её на странице заказов.')
            ->line('Спасибо, что используете наш сервис!');
    }

    /**
     * Get the array representation of the notification (для базы данных).
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'new_order',
            'title' => '🛒 Новая сделка!',
            'message' => $this->order->buyer->name . ' хочет купить у вас "' . ($this->order->listable->title ?? 'Объявление') . '"',
            'order_id' => $this->order->id,
            'buyer_id' => $this->order->buyer_id,
            'buyer_name' => $this->order->buyer->name,
            'seller_id' => $this->order->seller_id,
            'listing_title' => $this->order->listable->title ?? 'Объявление',
            'amount' => $this->order->amount ?? $this->order->listable->price ?? 0,
            'link' => route('orders.index'),
        ];
    }

    /**
     * Get the broadcastable representation of the notification (для WebSocket).
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'new_order',
            'title' => '🛒 Новая сделка!',
            'message' => $this->order->buyer->name . ' предложил сделку по вашему объявлению "' . ($this->order->listable->title ?? 'Объявление') . '"',
            'order_id' => $this->order->id,
            'buyer_name' => $this->order->buyer->name,
            'listing_title' => $this->order->listable->title ?? 'Объявление',
            'link' => route('orders.index'),
        ]);
    }

    /**
     * Determine which queue connection should be used.
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'mail-queue',
            'broadcast' => 'broadcast-queue',
        ];
    }
}