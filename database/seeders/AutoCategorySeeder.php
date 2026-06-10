<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AutoCategory;
use Illuminate\Support\Str;

class AutoCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Автомобили',
                'description' => 'Легковые автомобили всех марок',
                'icon' => 'car',
                'order' => 1
            ],
            [
                'name' => 'Грузовые',
                'description' => 'Грузовые автомобили и спецтехника',
                'icon' => 'truck',
                'order' => 2
            ],
            [
                'name' => 'Автобусы',
                'description' => 'Пассажирские автобусы и микроавтобусы',
                'icon' => 'bus',
                'order' => 3
            ],
            [
                'name' => 'Мотоциклы',
                'description' => 'Мотоциклы, скутеры и мопеды',
                'icon' => 'motorcycle',
                'order' => 4
            ],
            [
                'name' => 'Автозапчасти',
                'description' => 'Запчасти для авто и мототехники',
                'icon' => 'wrench',
                'order' => 5
            ],
            [
                'name' => 'Аренда авто',
                'description' => 'Аренда автомобилей на любой срок',
                'icon' => 'key',
                'order' => 6
            ],
        ];

        foreach ($categories as $category) {
            AutoCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('auto-' . $category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}