<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;

class LichnieVeschiListing extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'lichnie_veschi_listings';
    protected $listingType = 'lichnie_veschi';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'brand',
        'size',
        'color',
        'material',
        'condition',
        'gender',
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
        return $this->belongsTo(LichnieVeschiCategory::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}