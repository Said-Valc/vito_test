<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UslugiListing;
use App\Models\User;
use App\Models\Location;

class UslugiListingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $moscow = Location::where('name', 'Москва')->first();
        $spb = Location::where('name', 'Санкт-Петербург')->first();
        $alanya = Location::where('name', 'Аланья')->first();

        $listings = [
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Ремонт квартир под ключ в Москве',
                'description' => 'Профессиональный ремонт квартир и домов. Работаем с 2015 года. Гарантия на все работы. Дизайн-проект в подарок.',
                'service_type' => 'Ремонт и строительство',
                'price' => 15000,
                'price_type' => 'За квадратный метр',
                'portfolio' => json_encode([
                    'https://example.com/portfolio1.jpg',
                    'https://example.com/portfolio2.jpg'
                ]),
                'experience_years' => 9,
                'certificates' => json_encode([
                    'Сертификат соответствия',
                    'Лицензия Минстроя'
                ]),
                'city' => 'Москва',
                'address' => 'ул. Строителей, д. 10',
                'phone' => '+7 (999) 123-45-67',
                'email' => 'remont@mail.ru',
                'website' => 'https://remont-pro.ru',
                'work_schedule' => json_encode([
                    'Пн-Пт: 9:00-20:00',
                    'Сб: 10:00-18:00'
                ]),
                'has_guarantee' => true,
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
                'title' => 'Трансфер из аэропорта Анталья',
                'description' => 'Предлагаю трансфер из аэропорта Антальи до отеля. Комфортабельный Mercedes Vito. Встреча с табличкой. Русскоязычный водитель.',
                'service_type' => 'Перевозки',
                'price' => 3500,
                'price_type' => 'За услугу',
                'portfolio' => null,
                'experience_years' => 5,
                'certificates' => null,
                'city' => 'Аланья',
                'address' => 'Mahmutlar Mahallesi',
                'phone' => '+90 555 345 67 89',
                'email' => 'transfer@mail.ru',
                'website' => null,
                'work_schedule' => json_encode([
                    'Круглосуточно'
                ]),
                'has_guarantee' => false,
                'latitude' => $alanya ? $alanya->latitude : 36.5431,
                'longitude' => $alanya ? $alanya->longitude : 31.9991,
                'location_id' => $alanya ? $alanya->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($listings as $listing) {
            UslugiListing::create($listing);
        }
    }
}