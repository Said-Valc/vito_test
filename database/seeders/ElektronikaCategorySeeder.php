<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElektronikaCategory;
use Illuminate\Support\Str;

class ElektronikaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Телефоны',
                'description' => 'Мобильные телефоны и смартфоны',
                'icon' => 'phone',
                'brands' => ['Apple', 'Samsung', 'Xiaomi', 'Huawei', 'Google'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 1
            ],
            [
                'name' => 'Планшеты',
                'description' => 'Планшеты и электронные книги',
                'icon' => 'tablet',
                'brands' => ['Apple', 'Samsung', 'Lenovo', 'Huawei'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 2
            ],
            [
                'name' => 'Ноутбуки',
                'description' => 'Ноутбуки и ультрабуки',
                'icon' => 'laptop',
                'brands' => ['Apple', 'Dell', 'HP', 'Lenovo', 'Asus', 'Acer'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 3
            ],
            [
                'name' => 'Компьютерная техника',
                'description' => 'ПК, комплектующие, периферия',
                'icon' => 'pc',
                'brands' => ['Intel', 'AMD', 'NVIDIA', 'Logitech'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 4
            ],
            [
                'name' => 'Фото-видео',
                'description' => 'Фотоаппараты, видеокамеры, объективы',
                'icon' => 'camera',
                'brands' => ['Canon', 'Nikon', 'Sony', 'Panasonic'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 5
            ],
            [
                'name' => 'ТВ',
                'description' => 'Телевизоры и медиаплееры',
                'icon' => 'tv',
                'brands' => ['Samsung', 'LG', 'Sony', 'Philips'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 6
            ],
            [
                'name' => 'Бытовая техника',
                'description' => 'Техника для дома и кухни',
                'icon' => 'kitchen',
                'brands' => ['Bosch', 'Samsung', 'LG', 'Electrolux'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 7
            ],
        ];

        foreach ($categories as $category) {
            ElektronikaCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('elektronika-' . $category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'brands' => $category['brands'] ?? null,
                'conditions' => $category['conditions'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}