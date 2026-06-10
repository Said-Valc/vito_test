<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HobbyListing;
use App\Models\User;

class HobbyListingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $listings = [
            // Велосипеды (id: 1)
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Велосипед Author Victory, размер L',
                'description' => 'Продаю горный велосипед Author Victory. Алюминиевая рама, вилка воздушная, трансмиссия Shimano Deore. Размер рамы L (под рост 175-185).',
                'price' => 45000,
                'brand' => 'Author',
                'type' => 'Горный',
                'condition' => 'Хорошее',
                'size' => 'L (19")',
                'color' => 'Серый',
                'specifications' => json_encode([
                    'Рама' => 'Алюминий 6061',
                    'Вилка' => 'Воздушная 120мм',
                    'Трансмиссия' => 'Shimano Deore 2x10',
                    'Тормоза' => 'Гидравлические'
                ]),
                'photos' => json_encode(['bike1.jpg', 'bike2.jpg']),
                'city' => 'Москва',
                'phone' => '+7 (999) 678-90-12',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Рыбалка (id: 5)
            [
                'user_id' => $user->id,
                'category_id' => 5,
                'title' => 'Катушка Shimano Stradic 4000',
                'description' => 'Продаю катушку Shimano Stradic 4000. Использовалась один сезон, состояние отличное. Плавный ход, отличный фрикцион.',
                'price' => 12000,
                'brand' => 'Shimano',
                'type' => 'Рыбалка',
                'condition' => 'Хорошее',
                'size' => '4000',
                'color' => 'Серебристый',
                'specifications' => json_encode([
                    'Подшипники' => '5+1',
                    'Передаточное' => '5.0:1',
                    'Лескоемкость' => '0.3/200м'
                ]),
                'photos' => json_encode(['reel1.jpg']),
                'city' => 'Казань',
                'phone' => '+7 (999) 789-01-23',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($listings as $listing) {
            HobbyListing::create($listing);
        }
    }
}