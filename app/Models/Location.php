<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'country',
        'latitude',
        'longitude',
        'type',
        'population',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Получить расстояние до другой точки в км
     */
    public function distanceTo($latitude, $longitude): float
    {
        $earthRadius = 6371; // км

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 1);
    }

    /**
     * Поиск ближайших локаций
     */
    public static function findNearest($latitude, $longitude, $limit = 5)
    {
        return self::select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->where('is_active', true)
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    /**
     * Поиск локаций по названию
     */
    public static function searchByName(string $query, $limit = 10)
    {
        return self::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('region', 'like', "%{$query}%");
            })
            ->orderBy('population', 'desc')
            ->limit($limit)
            ->get();
    }
}