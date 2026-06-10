<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RabotaCategory;
use Illuminate\Support\Str;

class RabotaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Вакансии',
                'description' => 'Поиск сотрудников',
                'icon' => 'briefcase',
                'type' => 'vacancy',
                'order' => 1
            ],
            [
                'name' => 'Резюме',
                'description' => 'Поиск работы',
                'icon' => 'file-text',
                'type' => 'resume',
                'order' => 2
            ],
        ];

        foreach ($categories as $category) {
            RabotaCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('rabota-' . $category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'type' => $category['type'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}