<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LichnieVeschiCategory extends Model
{
    use HasFactory;

    protected $table = 'lichnie_veschi_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sizes',
        'conditions',
        'order',
        'is_active'
    ];

    protected $casts = [
        'sizes' => 'array',
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
        return $this->hasMany(LichnieVeschiListing::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}