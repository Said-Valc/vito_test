<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasRatings;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRatings, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function scopeFilter($query, array $filters)
    {
        if ($filters['search'] ?? false) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('email', 'like', '%' . request('search') . '%');
            });
        }

        if ($filters['user_role'] ?? false) {
            $query->where('role', request('user_role'));
        }
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }

    // Добавляем метод для пересчета рейтинга
    public function recalculateRating()
    {
        // Можно добавить кеширование
        // Cache::forget("user.{$this->id}.rating");
        
        // Просто обновляем модель (рейтинг вычисляется через аксессоры)
        $this->touch();
    }
     public function reviews()
    {
        return $this->hasMany(Review::class, 'seller_id');
    }

    /**
     * Отзывы, оставленные пользователем
     */
    public function writtenReviews()
    {
        return $this->hasMany(Review::class, 'buyer_id');
    }

    /**
     * Избранные объявления пользователя
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Проверить, добавлено ли объявление в избранное
     */
    public function hasFavorite(string $listableType, int $listableId): bool
    {
        return $this->favorites()
            ->where('listable_type', $listableType)
            ->where('listable_id', $listableId)
            ->exists();
    }
}