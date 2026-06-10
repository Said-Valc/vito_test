<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UslugiCategory;
use Illuminate\Support\Str;

class UslugiCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Услуги',
                'description' => 'Различные виды услуг',
                'icon' => 'handshake',
                'service_types' => [
                    'Ремонт и строительство',
                    'Клининг',
                    'Перевозки',
                    'Красота и здоровье',
                    'Обучение',
                    'Ремонт техники',
                    'Юридические услуги',
                    'Финансовые услуги'
                ],
                'order' => 1
            ],
        ];

        foreach ($categories as $category) {
            UslugiCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('uslugi'),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'service_types' => $category['service_types'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}