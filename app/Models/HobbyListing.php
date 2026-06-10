<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;
class HobbyListing extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'hobby_listings';
    protected $listingType = 'hobby';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'brand',
        'type',
        'condition',
        'size',
        'color',
        'specifications',
        'photos',
        'city',
        'phone',
        'is_active'
    ];

    protected $casts = [
        'specifications' => 'array',
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
        return $this->belongsTo(HobbyCategory::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}