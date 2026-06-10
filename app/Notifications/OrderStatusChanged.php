<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $action;
    protected ?string $initiator;

    /**
     * Create a new notification instance.
     *
     * @param Order $order
     * @param string $action - Тип действия: created, activated, buyer_confirmed, seller_confirmed, completed_buyer, completed_seller, cancelled
     * @param string|null $initiator - Кто инициировал действие: buyer, seller
     */
    public function __construct(
        public Order $order,
        string $action,
        ?string $initiator = null
    ) {
        $this->action = $action;
        $this->initiator = $initiator;
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
        $mail = (new MailMessage)
            ->subject($this->getTitle())
            ->greeting('Здравствуйте, ' . $notifiable->name . '!')
            ->line($this->getMessage());

        if ($this->action !== 'cancelled') {
            $mail->action('Перейти к сделке', route('orders.index'));
        }

        return $mail->line('Спасибо, что используете наш сервис!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'order_status_changed',
            'action' => $this->action,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'order_id' => $this->order->id,
            'order_status' => $this->order->status,
            'buyer_id' => $this->order->buyer_id,
            'seller_id' => $this->order->seller_id,
            'listing_title' => $this->order->listable->title ?? 'Объявление',
            'amount' => $this->order->amount ?? $this->order->listable->price ?? 0,
            'link' => route('orders.index'),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'order_status_changed',
            'action' => $this->action,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'order_id' => $this->order->id,
            'order_status' => $this->order->status,
            'link' => route('orders.index'),
        ]);
    }

    /**
     * Получить заголовок уведомления в зависимости от действия
     */
    protected function getTitle(): string
    {
        return match ($this->action) {
            'created' => '✅ Сделка создана',
            'activated' => '🚀 Сделка началась!',
            'buyer_confirmed' => '✅ Покупатель подтвердил получение',
            'seller_confirmed' => '📦 Продавец подтвердил отправку',
            'completed_buyer' => '🎉 Сделка завершена!',
            'completed_seller' => '🎉 Сделка успешно завершена!',
            'cancelled' => '❌ Сделка отменена',
            default => '📋 Статус сделки изменён',
        };
    }

    /**
     * Получить текст уведомления в зависимости от действия
     */
    protected function getMessage(): string
    {
        $listingTitle = $this->order->listable->title ?? 'Объявление';
        $buyerName = $this->order->buyer->name ?? 'Покупатель';
        $sellerName = $this->order->seller->name ?? 'Продавец';
        $amount = $this->formatAmount();

        return match ($this->action) {
            'created' => "Сделка по объявлению \"{$listingTitle}\" создана. Ожидайте подтверждения от второй стороны.",
            
            'activated' => $this->initiator === 'buyer'
                ? "Покупатель {$buyerName} начал сделку по объявлению \"{$listingTitle}\" на сумму {$amount}"
                : "Продавец {$sellerName} подтвердил начало сделки по объявлению \"{$listingTitle}\" на сумму {$amount}",
            
            'buyer_confirmed' => "Покупатель {$buyerName} подтвердил получение товара \"{$listingTitle}\". Ожидайте подтверждения от продавца.",
            
            'seller_confirmed' => "Продавец {$sellerName} подтвердил отправку товара \"{$listingTitle}\". Пожалуйста, подтвердите получение после доставки.",
            
            'completed_buyer' => "Поздравляем! Сделка по объявлению \"{$listingTitle}\" успешно завершена. Вы можете оставить отзыв о продавце.",
            
            'completed_seller' => "Поздравляем! Сделка по объявлению \"{$listingTitle}\" успешно завершена.",
            
            'cancelled' => $this->initiator === 'buyer'
                ? "Покупатель {$buyerName} отменил сделку по объявлению \"{$listingTitle}\""
                : "Продавец {$sellerName} отменил сделку по объявлению \"{$listingTitle}\"",
            
            default => "Статус сделки по объявлению \"{$listingTitle}\" изменён на \"{$this->getStatusText()}\"",
        };
    }

    /**
     * Форматировать сумму сделки
     */
    protected function formatAmount(): string
    {
        $amount = $this->order->amount ?? $this->order->listable->price ?? 0;
        return number_format($amount, 0, ',', ' ') . ' ₽';
    }

    /**
     * Получить текстовое представление статуса
     */
    protected function getStatusText(): string
    {
        return match ($this->order->status) {
            Order::STATUS_PENDING => 'Ожидает подтверждения',
            Order::STATUS_ACTIVE => 'Активная',
            Order::STATUS_COMPLETED => 'Завершена',
            Order::STATUS_CANCELLED => 'Отменена',
            Order::STATUS_DISPUTED => 'Спор',
            default => $this->order->status,
        };
    }
}