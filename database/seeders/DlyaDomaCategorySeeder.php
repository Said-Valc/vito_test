<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DlyaDomaCategory;
use Illuminate\Support\Str;

class DlyaDomaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Для дома',
                'description' => 'Мебель, посуда, текстиль',
                'icon' => 'home',
                'materials' => ['Дерево', 'Металл', 'Стекло', 'Ткань', 'Пластик'],
                'conditions' => ['Новый', 'Как новый', 'Хорошее', 'Среднее'],
                'order' => 1
            ],
        ];

        foreach ($categories as $category) {
            DlyaDomaCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('dlya-doma'),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'materials' => $category['materials'],
                'conditions' => $category['conditions'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}