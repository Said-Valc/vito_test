<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'listable_type',
        'listable_id',
        'amount',
        'status',
        'buyer_confirmed_at',
        'seller_confirmed_at',
        'completed_at',
        'cancelled_at',
        'buyer_comment',
        'seller_comment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'buyer_confirmed_at' => 'datetime',
        'seller_confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Статусы заказов
    const STATUS_PENDING = 'pending';      // Ожидает подтверждения
    const STATUS_ACTIVE = 'active';        // Активная сделка
    const STATUS_COMPLETED = 'completed';  // Завершена
    const STATUS_CANCELLED = 'cancelled';  // Отменена
    const STATUS_DISPUTED = 'disputed';    // Спор

    // Связь с покупателем
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Связь с продавцом
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Полиморфная связь с объявлением
    public function listable()
    {
        return $this->morphTo();
    }

    // Проверка, может ли пользователь оставить отзыв
    public function canReview($userId)
    {
        return $this->status === self::STATUS_COMPLETED && 
               ($this->buyer_id === $userId || $this->seller_id === $userId);
    }

    // Завершить сделку (когда обе стороны подтвердили)
    public function complete()
    {
        if ($this->buyer_confirmed_at && $this->seller_confirmed_at && $this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
            $this->save();
            
            // Автоматически деактивируем объявление
            if ($this->listable) {
                $this->listable->update(['is_active' => false]);
            }
            
            return true;
        }
        return false;
    }

    // Scope для активных сделок
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope для завершенных
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}