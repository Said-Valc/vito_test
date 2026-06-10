<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LichnieVeschiListing;
use App\Models\User;

class LichnieVeschiListingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $listings = [
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Кожаная куртка ZARA, размер M',
                'description' => 'Продаю кожаную куртку ZARA. Носила один сезон, состояние отличное. Натуральная кожа, подкладка вискоза. Размер M (48-50).',
                'price' => 8500,
                'brand' => 'ZARA',
                'size' => 'M',
                'color' => 'Черный',
                'material' => 'Натуральная кожа',
                'condition' => 'Как новый',
                'gender' => 'Женский',
                'photos' => json_encode(['jacket1.jpg', 'jacket2.jpg']),
                'city' => 'Санкт-Петербург',
                'phone' => '+7 (999) 234-56-78',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($listings as $listing) {
            LichnieVeschiListing::create($listing);
        }
    }
}