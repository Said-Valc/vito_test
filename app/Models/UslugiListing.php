<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;

class UslugiListing extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'uslugi_listings';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'service_type',
        'price_type',
        'portfolio',
        'experience_years',
        'certificates',
        'city',
        'address',
        'phone',
        'email',
        'website',
        'work_schedule',
        'has_guarantee',
        'is_active'
    ];

    protected $casts = [
        'portfolio' => 'array',
        'certificates' => 'array',
        'work_schedule' => 'array',
        'has_guarantee' => 'boolean',
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
        return $this->belongsTo(UslugiCategory::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}