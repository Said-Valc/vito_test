<?php

namespace App\Traits;

use App\Models\Review;

trait HasRatings
{
    /**
     * Получить все полученные отзывы
     */
    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'seller_id');
    }

    /**
     * Получить все оставленные отзывы
     */
    public function leftReviews()
    {
        return $this->hasMany(Review::class, 'buyer_id');
    }

    /**
     * Получить средний рейтинг продавца
     */
    public function getRatingAttribute()
    {
        $avg = $this->receivedReviews()
            ->where('is_approved', true)
            ->avg('rating');
        
        return round($avg ?? 0, 1);
    }

    /**
     * Получить количество отзывов
     */
    public function getRatingCountAttribute()
    {
        return $this->receivedReviews()
            ->where('is_approved', true)
            ->count();
    }

    /**
     * Получить распределение оценок
     */
    public function getRatingDistributionAttribute()
    {
        $distribution = $this->receivedReviews()
            ->where('is_approved', true)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[$i] = $distribution[$i] ?? 0;
        }
        return $result;
    }

    /**
     * Получить процент положительных отзывов (4-5 звезд)
     */
    public function getPositiveRatingPercentAttribute()
    {
        $total = $this->rating_count;
        if ($total === 0) return 0;
        
        $positive = $this->receivedReviews()
            ->where('is_approved', true)
            ->whereIn('rating', [4, 5])
            ->count();
            
        return round(($positive / $total) * 100);
    }

    /**
     * Пересчитать рейтинг (можно добавить кеширование)
     */
    public function recalculateRating()
    {
        // Очищаем кеш если используется
        // Cache::forget("user.{$this->id}.rating");
        
        // Просто обновляем timestamps
        $this->touch();
    }
}