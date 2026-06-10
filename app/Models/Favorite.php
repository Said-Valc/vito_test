<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listable_type',
        'listable_id',
    ];

    /**
     * Пользователь, добавивший в избранное
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Полиморфная связь с объявлением
     */
    public function listable(): MorphTo
    {
        return $this->morphTo();
    }
}