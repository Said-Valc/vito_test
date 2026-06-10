<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasImages;
use App\Traits\Filterable;
class RabotaVacancy extends Model
{
    use HasFactory, HasImages, Filterable;

    protected $table = 'rabota_vacancies';
    protected $listingType = 'vacancy';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'company_name',
        'company_description',
        'salary_from',
        'salary_to',
        'salary_type',
        'employment_type',
        'work_schedule',
        'experience',
        'education',
        'requirements',
        'conditions',
        'city',
        'address',
        'phone',
        'email',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'requirements' => 'array',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'salary_from' => 'decimal:2',
        'salary_to' => 'decimal:2',
        'expires_at' => 'date'
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