<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'listable_type',
        'listable_id',
        'rating',
        'comment',
        'is_verified',
        'is_approved',
        'is_edited',
        'seller_response',
        'responded_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'is_edited' => 'boolean',
        'responded_at' => 'datetime',
        'rating' => 'integer',
    ];

    /**
     * Boot метод для автоматического пересчета рейтинга
     */
    protected static function booted()
    {
        static::created(function ($review) {
            \Log::info('Review created', ['review' => $review->toArray()]);
            if ($review->is_approved) {
                $review->seller->recalculateRating();
            }
        });

        static::updated(function ($review) {
            \Log::info('Review updated', ['review' => $review->toArray()]);
            if ($review->wasChanged('rating') || $review->wasChanged('is_approved')) {
                $review->seller->recalculateRating();
            }
        });

        static::deleted(function ($review) {
            \Log::info('Review deleted', ['review_id' => $review->id]);
            $review->seller->recalculateRating();
        });
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function listable()
    {
        return $this->morphTo();
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeForSeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}