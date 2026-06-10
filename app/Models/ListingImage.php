<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'position',
        'is_main'
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'position' => 'integer'
    ];

    /**
     * Получить родительское объявление (полиморфная связь)
     */
    public function listable()
    {
        return $this->morphTo();
    }

    /**
     * Получить полный URL изображения
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}