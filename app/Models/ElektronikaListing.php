<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;

class ElektronikaListing extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'elektronika_listings';
    protected $listingType = 'elektronika';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'brand',
        'model',
        'condition',
        'specifications',
        'warranty',
        'is_original',
        'photos',
        'city',
        'phone',
        'is_active'
    ];

    protected $casts = [
        'specifications' => 'array',
        'photos' => 'array',
        'is_original' => 'boolean',
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
        return $this->belongsTo(ElektronikaCategory::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}