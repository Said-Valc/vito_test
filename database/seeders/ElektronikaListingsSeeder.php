<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElektronikaListing;
use App\Models\User;

class ElektronikaListingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $listings = [
            // Телефоны (id: 1)
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'iPhone 15 Pro Max 256GB, новый',
                'description' => 'Продаю iPhone 15 Pro Max 256GB. Новый, запечатанная коробка. Цвет натуральный титан. Гарантия 1 год.',
                'price' => 120000,
                'brand' => 'Apple',
                'model' => 'iPhone 15 Pro Max',
                'condition' => 'Новый',
                'specifications' => json_encode([
                    'Экран' => '6.7" Super Retina XDR',
                    'Процессор' => 'A17 Pro',
                    'Память' => '256GB',
                    'Камера' => '48 МП',
                    'Цвет' => 'Натуральный титан'
                ]),
                'warranty' => 'Есть гарантия',
                'is_original' => true,
                'photos' => json_encode(['iphone1.jpg', 'iphone2.jpg']),
                'city' => 'Москва',
                'phone' => '+7 (999) 456-78-90',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Ноутбуки (id: 3)
            [
                'user_id' => $user->id,
                'category_id' => 3,
                'title' => 'MacBook Pro 14 M3 Max, 2023',
                'description' => 'MacBook Pro 14 с чипом M3 Max. 32GB RAM, 1TB SSD. Состояние идеальное, в использовании 2 месяца. Полный комплект, чек.',
                'price' => 280000,
                'brand' => 'Apple',
                'model' => 'MacBook Pro M3 Max',
                'condition' => 'Б/У',
                'specifications' => json_encode([
                    'Экран' => '14" Liquid Retina XDR',
                    'Процессор' => 'M3 Max (16 ядер)',
                    'Память' => '32GB',
                    'Накопитель' => '1TB SSD',
                    'Год' => '2023'
                ]),
                'warranty' => 'Нет гарантии',
                'is_original' => true,
                'photos' => json_encode(['macbook1.jpg', 'macbook2.jpg']),
                'city' => 'Санкт-Петербург',
                'phone' => '+7 (999) 567-89-01',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($listings as $listing) {
            ElektronikaListing::create($listing);
        }
    }
}