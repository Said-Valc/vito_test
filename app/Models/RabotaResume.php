<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;
class RabotaResume extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'rabota_resumes';
    protected $listingType = 'resume';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'about_me',
        'full_name',
        'age',
        'gender',
        'education',
        'specialization',
        'work_experience',
        'skills',
        'languages',
        'salary_expectation',
        'employment_type',
        'work_schedule',
        'city',
        'phone',
        'email',
        'is_active'
    ];

    protected $casts = [
        'work_experience' => 'array',
        'skills' => 'array',
        'languages' => 'array',
        'is_active' => 'boolean',
        'salary_expectation' => 'decimal:2'
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
        return $this->belongsTo(RabotaCategory::class, 'category_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'listable');
    }
}