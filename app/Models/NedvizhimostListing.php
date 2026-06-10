<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;

class NedvizhimostListing extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'nedvizhimost_listings';
    protected $listingType = 'nedvizhimost';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'action',
        'rooms',
        'area_total',
        'area_living',
        'area_kitchen',
        'floor',
        'floors_total',
        'building_type',
        'year_built',
        'condition',
        'has_balcony',
        'has_parking',
        'has_furniture',
        'address',
        'city',
        'district',
        'photos',
        'phone',
        'is_active'
    ];

    protected $casts = [
        'photos' => 'array',
        'has_balcony' => 'boolean',
        'has_parking' => 'boolean',
        'has_furniture' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'area_total' => 'decimal:2',
        'area_living' => 'decimal:2',
        'area_kitchen' => 'decimal:2'
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
        return $this->belongsTo(NedvizhimostCategory::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}