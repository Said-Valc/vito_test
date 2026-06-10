<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;

class DlyaDomaListing extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'dlya_doma_listings';
    protected $listingType = 'dlya_doma';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'type',
        'brand',
        'material',
        'color',
        'size',
        'condition',
        'photos',
        'city',
        'phone',
        'is_active'
    ];

    protected $casts = [
        'photos' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];

    protected $appends = [
        'main_image_url',
        'images_urls',
        'images_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(DlyaDomaCategory::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
    
}