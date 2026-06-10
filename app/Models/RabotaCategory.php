<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RabotaCategory extends Model
{
    use HasFactory;

    protected $table = 'rabota_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function vacancies()
    {
        return $this->hasMany(RabotaVacancy::class, 'category_id');
    }

    public function resumes()
    {
        return $this->hasMany(RabotaResume::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}