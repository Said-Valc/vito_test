<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HobbyCategory;
use Illuminate\Support\Str;

class HobbyCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Велосипеды',
                'description' => 'Горные, шоссейные, городские велосипеды',
                'icon' => 'bicycle',
                'sport_types' => ['Горный', 'Шоссейный', 'Городской', 'Детский'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 1
            ],
            [
                'name' => 'Ролики, скейтборд',
                'description' => 'Роликовые коньки, скейтборды, лонгборды',
                'icon' => 'skateboard',
                'sport_types' => ['Ролики', 'Скейтборд', 'Лонгборд'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 2
            ],
            [
                'name' => 'Самокаты',
                'description' => 'Самокаты детские и взрослые',
                'icon' => 'scooter',
                'sport_types' => ['Детский', 'Взрослый', 'Электро'],
                'conditions' => ['Новый', 'Б/У'],
                'order' => 3
            ],
            [
                'name' => 'Спортивная экипировка',
                'description' => 'Форма, защита, инвентарь',
                'icon' => 'sports',
                'conditions' => ['Новый', 'Б/У'],
                'order' => 4
            ],
            [
                'name' => 'Охота и рыбалка',
                'description' => 'Снаряжение для охоты и рыбалки',
                'icon' => 'fishing',
                'conditions' => ['Новый', 'Б/У'],
                'order' => 5
            ],
            [
                'name' => 'Туризм и отдых на природе',
                'description' => 'Палатки, спальники, рюкзаки',
                'icon' => 'camping',
                'conditions' => ['Новый', 'Б/У'],
                'order' => 6
            ],
            [
                'name' => 'Теннис',
                'description' => 'Ракетки, мячи, аксессуары',
                'icon' => 'tennis',
                'conditions' => ['Новый', 'Б/У'],
                'order' => 7
            ],
            [
                'name' => 'Тренажеры и фитнес',
                'description' => 'Спортивные тренажеры, инвентарь для фитнеса',
                'icon' => 'gym',
                'conditions' => ['Новый', 'Б/У'],
                'order' => 8
            ],
            [
                'name' => 'Спортивное питание',
                'description' => 'Протеины, гейнеры, витамины',
                'icon' => 'nutrition',
                'conditions' => ['Новый'],
                'order' => 9
            ],
            [
                'name' => 'Другое',
                'description' => 'Прочие товары для хобби и отдыха',
                'icon' => 'other',
                'conditions' => ['Новый', 'Б/У'],
                'order' => 10
            ],
        ];

        foreach ($categories as $category) {
            HobbyCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('hobby-' . $category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'sport_types' => $category['sport_types'] ?? null,
                'conditions' => $category['conditions'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}