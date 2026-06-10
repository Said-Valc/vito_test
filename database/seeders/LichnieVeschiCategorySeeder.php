<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LichnieVeschiCategory;
use Illuminate\Support\Str;

class LichnieVeschiCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Личные вещи',
                'description' => 'Одежда, обувь, аксессуары',
                'icon' => 'shirt',
                'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'conditions' => ['Новый', 'Как новый', 'Хорошее', 'Среднее'],
                'order' => 1
            ],
        ];

        foreach ($categories as $category) {
            LichnieVeschiCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('lichnie-veschi'),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'sizes' => $category['sizes'],
                'conditions' => $category['conditions'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}