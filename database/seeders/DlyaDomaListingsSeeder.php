<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DlyaDomaListing;
use App\Models\User;
use App\Models\Location;

class DlyaDomaListingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $moscow = Location::where('name', 'Москва')->first();
        $ekaterinburg = Location::where('name', 'Екатеринбург')->first();

        $listings = [
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Диван угловой IKEA, новый',
                'description' => 'Продаю угловой диван IKEA. Покупали месяц назад, не подошел по размеру. В идеальном состоянии, в упаковке. Цвет бежевый, ткань износостойкая.',
                'price' => 35000,
                'type' => 'Мебель',
                'brand' => 'IKEA',
                'material' => 'Ткань, металл',
                'color' => 'Бежевый',
                'size' => 'Угловой 250x150',
                'condition' => 'Новый',
                'city' => 'Москва',
                'phone' => '+7 (999) 345-67-89',
                'latitude' => $moscow ? $moscow->latitude : 55.7558,
                'longitude' => $moscow ? $moscow->longitude : 37.6173,
                'location_id' => $moscow ? $moscow->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Кухонный гарнитур, Екатеринбург',
                'description' => 'Продаю кухонный гарнитур. Состояние хорошее, все ящики работают. Встроенная техника: духовка, варочная панель. Размеры: 3м x 2м.',
                'price' => 85000,
                'type' => 'Мебель',
                'brand' => 'Мария',
                'material' => 'МДФ, пластик',
                'color' => 'Белый глянец',
                'size' => '300x200x85',
                'condition' => 'Хорошее',
                'city' => 'Екатеринбург',
                'phone' => '+7 (999) 678-90-12',
                'latitude' => $ekaterinburg ? $ekaterinburg->latitude : 56.8389,
                'longitude' => $ekaterinburg ? $ekaterinburg->longitude : 60.6057,
                'location_id' => $ekaterinburg ? $ekaterinburg->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($listings as $listing) {
            DlyaDomaListing::create($listing);
        }
    }
}