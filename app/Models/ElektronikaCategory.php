<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ElektronikaCategory extends Model
{
    use HasFactory;

    protected $table = 'elektronika_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'brands',
        'conditions',
        'order',
        'is_active'
    ];

    protected $casts = [
        'brands' => 'array',
        'conditions' => 'array',
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

    public function listings()
    {
        return $this->hasMany(ElektronikaListing::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}