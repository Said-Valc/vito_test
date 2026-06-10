<?php

namespace App\Services;

use App\Models\AutoListing;
use App\Models\NedvizhimostListing;
use App\Models\ElektronikaListing;
use App\Models\HobbyListing;
use App\Models\RabotaVacancy;
use App\Models\RabotaResume;
use App\Models\UslugiListing;
use App\Models\LichnieVeschiListing;
use App\Models\DlyaDomaListing;

class ListingService
{
    public function create(array $data)
    {
        $baseData = [
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'] ?? null,
            'city' => $data['city'],
            'phone' => $data['phone'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'is_active' => true,
        ];

        return match ($data['category_type']) {

            'auto' => AutoListing::create(array_merge($baseData, [
                'brand' => $data['brand'],
                'model' => $data['model'],
                'year' => $data['year'],
                'mileage' => $data['mileage'] ?? null,
                'fuel_type' => $data['fuel_type'] ?? null,
                'transmission' => $data['transmission'] ?? null,
                'color' => $data['color'] ?? null,
            ])),

            'nedvizhimost' => NedvizhimostListing::create(array_merge($baseData, [
                'action' => $data['action'],
                'rooms' => $data['rooms'] ?? null,
                'area_total' => $data['area_total'] ?? null,
                'floor' => $data['floor'] ?? null,
                'floors_total' => $data['floors_total'] ?? null,
                'address' => $data['address'],
            ])),

            'elektronika' => ElektronikaListing::create(array_merge($baseData, [
                'brand' => $data['brand'],
                'model' => $data['model'],
                'condition' => $data['condition'],
            ])),

            'hobby' => HobbyListing::create($baseData),

            'uslugi' => UslugiListing::create(array_merge($baseData, [
                'service_type' => $data['service_type'],
                'price_type' => $data['price_type'] ?? null,
            ])),

            'lichnie_veschi' => LichnieVeschiListing::create($baseData),

            'dlya_doma' => DlyaDomaListing::create($baseData),

            default => null
        };
    }
}