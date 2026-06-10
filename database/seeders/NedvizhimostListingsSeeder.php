<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NedvizhimostListing;
use App\Models\User;

class NedvizhimostListingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $listings = [
            // Квартира (id: 1)
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => '3-комнатная квартира в центре',
                'description' => 'Продается просторная 3-комнатная квартира в центре города. 18 этаж, отличный вид, развитая инфраструктура. Ремонт евро, новая сантехника, встроенная кухня.',
                'price' => 12500000,
                'action' => 'Продать',
                'rooms' => 3,
                'area_total' => 85.5,
                'area_living' => 52.3,
                'area_kitchen' => 12.5,
                'floor' => 18,
                'floors_total' => 25,
                'building_type' => 'Монолитный',
                'year_built' => 2020,
                'condition' => 'Евроремонт',
                'has_balcony' => true,
                'has_parking' => true,
                'has_furniture' => true,
                'address' => 'ул. Ленина, д. 10, кв. 185',
                'city' => 'Москва',
                'district' => 'Центральный',
                'photos' => json_encode(['flat1.jpg', 'flat2.jpg', 'flat3.jpg']),
                'phone' => '+7 (999) 123-45-67',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Комната (id: 2)
            [
                'user_id' => $user->id,
                'category_id' => 2,
                'title' => 'Сдам комнату в 2-комнатной квартире',
                'description' => 'Сдам уютную комнату в 2-комнатной квартире. Все удобства, хорошие соседи. Рядом метро и магазины. Для одного человека.',
                'price' => 20000,
                'action' => 'Сдам',
                'rooms' => 1,
                'area_total' => 18.0,
                'area_living' => 18.0,
                'area_kitchen' => 10.0,
                'floor' => 5,
                'floors_total' => 9,
                'building_type' => 'Панельный',
                'year_built' => 2010,
                'condition' => 'Хороший',
                'has_balcony' => true,
                'has_parking' => false,
                'has_furniture' => true,
                'address' => 'ул. Московская, д. 5, кв. 78',
                'city' => 'Санкт-Петербург',
                'district' => 'Московский',
                'photos' => json_encode(['room1.jpg']),
                'phone' => '+7 (999) 234-56-78',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Дом (id: 3)
            [
                'user_id' => $user->id,
                'category_id' => 3,
                'title' => 'Продается коттедж 250 кв.м с участком',
                'description' => 'Продается двухэтажный коттедж с участком 10 соток. Газ, свет, вода центральные. Сад, баня, гараж. Идеально для семьи.',
                'price' => 8500000,
                'action' => 'Продать',
                'rooms' => 5,
                'area_total' => 250.0,
                'area_living' => 180.0,
                'area_kitchen' => 25.0,
                'floor' => 1,
                'floors_total' => 2,
                'building_type' => 'Кирпичный',
                'year_built' => 2018,
                'condition' => 'Отличный',
                'has_balcony' => true,
                'has_parking' => true,
                'has_furniture' => false,
                'address' => 'Коттеджный поселок "Березки", уч. 15',
                'city' => 'Краснодар',
                'district' => 'Пригород',
                'photos' => json_encode(['house1.jpg', 'house2.jpg']),
                'phone' => '+7 (999) 345-67-89',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Коммерческая недвижимость (id: 4)
            [
                'user_id' => $user->id,
                'category_id' => 4,
                'title' => 'Офисное помещение в бизнес-центре',
                'description' => 'Сдам офисное помещение в бизнес-центре класса А. Полная отделка, кондиционирование, охрана, парковка. Идеально под офис.',
                'price' => 120000,
                'action' => 'Сдам',
                'rooms' => 4,
                'area_total' => 85.0,
                'area_living' => null,
                'area_kitchen' => null,
                'floor' => 5,
                'floors_total' => 12,
                'building_type' => 'Монолитный',
                'year_built' => 2021,
                'condition' => 'Евроремонт',
                'has_balcony' => false,
                'has_parking' => true,
                'has_furniture' => true,
                'address' => 'Бизнес-центр "Москва-Сити", офис 512',
                'city' => 'Москва',
                'district' => 'Центральный',
                'photos' => json_encode(['office1.jpg', 'office2.jpg']),
                'phone' => '+7 (999) 456-78-90',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Земельный участок (id: 5)
            [
                'user_id' => $user->id,
                'category_id' => 5,
                'title' => 'Земельный участок 15 соток под ИЖС',
                'description' => 'Продается земельный участок 15 соток под ИЖС. Ровный, сухой, коммуникации по границе. Отличное место для строительства дома.',
                'price' => 2500000,
                'action' => 'Продать',
                'rooms' => null,
                'area_total' => 1500.0,
                'area_living' => null,
                'area_kitchen' => null,
                'floor' => null,
                'floors_total' => null,
                'building_type' => null,
                'year_built' => null,
                'condition' => null,
                'has_balcony' => false,
                'has_parking' => false,
                'has_furniture' => false,
                'address' => 'СНТ "Ромашка", уч. 45',
                'city' => 'Ленинградская область',
                'district' => 'Всеволожский район',
                'photos' => json_encode(['land1.jpg', 'land2.jpg']),
                'phone' => '+7 (999) 567-89-01',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($listings as $listing) {
            NedvizhimostListing::create($listing);
        }
    }
}