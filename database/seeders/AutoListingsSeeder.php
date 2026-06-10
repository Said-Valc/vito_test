<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AutoListing;
use App\Models\User;
use App\Models\Location;

class AutoListingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        
        // Получаем локации для разных городов
        $moscow = Location::where('name', 'Москва')->first();
        $spb = Location::where('name', 'Санкт-Петербург')->first();
        $kazan = Location::where('name', 'Казань')->first();
        $novosibirsk = Location::where('name', 'Новосибирск')->first();
        $ekaterinburg = Location::where('name', 'Екатеринбург')->first();
        $antalya = Location::where('name', 'Анталья')->first();
        $istanbul = Location::where('name', 'Стамбул')->first();
        $alanya = Location::where('name', 'Аланья')->first();

        $listings = [
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Toyota Camry 2020, идеальное состояние',
                'description' => 'Продаю Toyota Camry в идеальном состоянии. Полный привод, климат-контроль, кожаный салон. Не бита, не крашена. Один владелец. Полное обслуживание у дилера.',
                'price' => 2500000,
                'brand' => 'Toyota',
                'model' => 'Camry',
                'year' => 2020,
                'mileage' => 45000,
                'fuel_type' => 'Бензин',
                'engine_capacity' => '2.5',
                'transmission' => 'Автомат',
                'drive_type' => 'Передний',
                'color' => 'Белый',
                'body_type' => 'Седан',
                'condition' => 'Отличное',
                'is_accident' => false,
                'is_credit' => false,
                'city' => 'Москва',
                'phone' => '+7 (999) 123-45-67',
                'latitude' => $moscow ? $moscow->latitude : 55.7558,
                'longitude' => $moscow ? $moscow->longitude : 37.6173,
                'location_id' => $moscow ? $moscow->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $user->id,
                'category_id' => 2,
                'title' => 'Грузовик Mercedes-Benz Actros 1845',
                'description' => 'Продам грузовик Mercedes-Benz Actros 1845. 2019 год, пробег 320000 км. Евро-5, рефрижератор, спальник. В эксплуатации с 2020 года, обслуживался вовремя.',
                'price' => 5500000,
                'brand' => 'Mercedes-Benz',
                'model' => 'Actros 1845',
                'year' => 2019,
                'mileage' => 320000,
                'fuel_type' => 'Дизель',
                'engine_capacity' => '12.8',
                'transmission' => 'Механика',
                'drive_type' => 'Задний',
                'color' => 'Белый',
                'body_type' => 'Фургон',
                'condition' => 'Хорошее',
                'is_accident' => false,
                'is_credit' => false,
                'city' => 'Санкт-Петербург',
                'phone' => '+7 (999) 234-56-78',
                'latitude' => $spb ? $spb->latitude : 59.9343,
                'longitude' => $spb ? $spb->longitude : 30.3351,
                'location_id' => $spb ? $spb->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $user->id,
                'category_id' => 4,
                'title' => 'Harley-Davidson Sportster Iron 883',
                'description' => 'Продам легендарный Harley-Davidson Sportster Iron 883. 2022 год, пробег всего 5000 км. Тюнинг: выхлоп Vance & Hines, кожаные сумки. В отличном состоянии.',
                'price' => 1800000,
                'brand' => 'Harley-Davidson',
                'model' => 'Sportster Iron 883',
                'year' => 2022,
                'mileage' => 5000,
                'fuel_type' => 'Бензин',
                'engine_capacity' => '0.883',
                'transmission' => 'Механика',
                'drive_type' => 'Задний',
                'color' => 'Черный',
                'body_type' => 'Круизер',
                'condition' => 'Отличное',
                'is_accident' => false,
                'is_credit' => false,
                'city' => 'Казань',
                'phone' => '+7 (999) 345-67-89',
                'latitude' => $kazan ? $kazan->latitude : 55.7879,
                'longitude' => $kazan ? $kazan->longitude : 49.1233,
                'location_id' => $kazan ? $kazan->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $user->id,
                'category_id' => 5,
                'title' => 'Двигатель Toyota 2JZ-GTE',
                'description' => 'Продаю контрактный двигатель Toyota 2JZ-GTE. Приехал из Японии, пробег 80000 км. В комплекте: навесное, турбины, форсунки. Проверен, масло не ест, не дымит.',
                'price' => 450000,
                'brand' => 'Toyota',
                'model' => '2JZ-GTE',
                'year' => 2015,
                'mileage' => 80000,
                'fuel_type' => 'Бензин',
                'engine_capacity' => '3.0',
                'transmission' => null,
                'drive_type' => null,
                'color' => null,
                'body_type' => null,
                'condition' => 'Хорошее',
                'is_accident' => false,
                'is_credit' => false,
                'city' => 'Новосибирск',
                'phone' => '+7 (999) 456-78-90',
                'latitude' => $novosibirsk ? $novosibirsk->latitude : 55.0084,
                'longitude' => $novosibirsk ? $novosibirsk->longitude : 82.9357,
                'location_id' => $novosibirsk ? $novosibirsk->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $user->id,
                'category_id' => 6,
                'title' => 'Сдам в аренду BMW X5 на сутки',
                'description' => 'Сдам в аренду BMW X5 2021 года. Полный привод, кожаный салон, панорама. Цена указана за сутки. Залог 10000 рублей. Возможна подача к аэропорту.',
                'price' => 5000,
                'brand' => 'BMW',
                'model' => 'X5',
                'year' => 2021,
                'mileage' => 25000,
                'fuel_type' => 'Бензин',
                'engine_capacity' => '3.0',
                'transmission' => 'Автомат',
                'drive_type' => 'Полный',
                'color' => 'Черный',
                'body_type' => 'Внедорожник',
                'condition' => 'Отличное',
                'is_accident' => false,
                'is_credit' => false,
                'city' => 'Москва',
                'phone' => '+7 (999) 567-89-01',
                'latitude' => $moscow ? $moscow->latitude : 55.7558,
                'longitude' => $moscow ? $moscow->longitude : 37.6173,
                'location_id' => $moscow ? $moscow->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Добавим объявления в Турции
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Toyota Corolla 2023, Анталья',
                'description' => 'Продаю Toyota Corolla 2023 года в Анталье. Автомобиль в отличном состоянии, пробег 15000 км. Идеально подходит для города и путешествий по Турции.',
                'price' => 32000, // в долларах/евро
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2023,
                'mileage' => 15000,
                'fuel_type' => 'Бензин',
                'engine_capacity' => '1.6',
                'transmission' => 'Автомат',
                'drive_type' => 'Передний',
                'color' => 'Красный',
                'body_type' => 'Седан',
                'condition' => 'Отличное',
                'is_accident' => false,
                'is_credit' => false,
                'city' => 'Анталья',
                'phone' => '+90 555 123 45 67',
                'latitude' => $antalya ? $antalya->latitude : 36.8969,
                'longitude' => $antalya ? $antalya->longitude : 30.7133,
                'location_id' => $antalya ? $antalya->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $user->id,
                'category_id' => 1,
                'title' => 'Ford Tourneo Courier 2024, Стамбул',
                'description' => 'Продам Ford Tourneo Courier 2024 в Стамбуле. Отличный семейный автомобиль. 7 мест, дизель, экономичный расход. Пробег 8000 км.',
                'price' => 28000,
                'brand' => 'Ford',
                'model' => 'Tourneo Courier',
                'year' => 2024,
                'mileage' => 8000,
                'fuel_type' => 'Дизель',
                'engine_capacity' => '1.5',
                'transmission' => 'Механика',
                'drive_type' => 'Передний',
                'color' => 'Синий',
                'body_type' => 'Минивэн',
                'condition' => 'Отличное',
                'is_accident' => false,
                'is_credit' => false,
                'city' => 'Стамбул',
                'phone' => '+90 555 234 56 78',
                'latitude' => $istanbul ? $istanbul->latitude : 41.0082,
                'longitude' => $istanbul ? $istanbul->longitude : 28.9784,
                'location_id' => $istanbul ? $istanbul->id : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($listings as $listing) {
            AutoListing::create($listing);
        }
    }
}