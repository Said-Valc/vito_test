<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NedvizhimostCategory;
use Illuminate\Support\Str;

class NedvizhimostCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];
        $order = 1;

        // Квартиры с разными действиями
        $kvartiryActions = [
            ['name' => 'Купить квартиру', 'action' => 'Купить'],
            ['name' => 'Продать квартиру', 'action' => 'Продать'],
            ['name' => 'Снять квартиру', 'action' => 'Снять'],
            ['name' => 'Сдать квартиру', 'action' => 'Сдам'],
        ];

        foreach ($kvartiryActions as $item) {
            $categories[] = [
                'name' => $item['name'],
                'description' => $item['name'],
                'icon' => 'building',
                'action' => $item['action'],
                'order' => $order++,
                'group' => 'Квартиры'
            ];
        }

        // Комнаты с разными действиями
        $komnatyActions = [
            ['name' => 'Купить комнату', 'action' => 'Купить'],
            ['name' => 'Продать комнату', 'action' => 'Продать'],
            ['name' => 'Снять комнату', 'action' => 'Снять'],
            ['name' => 'Сдать комнату', 'action' => 'Сдам'],
        ];

        foreach ($komnatyActions as $item) {
            $categories[] = [
                'name' => $item['name'],
                'description' => $item['name'],
                'icon' => 'door-open',
                'action' => $item['action'],
                'order' => $order++,
                'group' => 'Комнаты'
            ];
        }

        // Дома с разными действиями
        $domaActions = [
            ['name' => 'Купить дом', 'action' => 'Купить'],
            ['name' => 'Продать дом', 'action' => 'Продать'],
            ['name' => 'Снять дом', 'action' => 'Снять'],
            ['name' => 'Сдать дом', 'action' => 'Сдам'],
        ];

        foreach ($domaActions as $item) {
            $categories[] = [
                'name' => $item['name'],
                'description' => $item['name'],
                'icon' => 'house',
                'action' => $item['action'],
                'order' => $order++,
                'group' => 'Дома'
            ];
        }

        // Коммерческая недвижимость
        $commercialActions = [
            ['name' => 'Купить коммерческую недвижимость', 'action' => 'Купить'],
            ['name' => 'Продать коммерческую недвижимость', 'action' => 'Продать'],
            ['name' => 'Снять коммерческую недвижимость', 'action' => 'Снять'],
            ['name' => 'Сдать коммерческую недвижимость', 'action' => 'Сдам'],
        ];

        foreach ($commercialActions as $item) {
            $categories[] = [
                'name' => $item['name'],
                'description' => $item['name'],
                'icon' => 'briefcase',
                'action' => $item['action'],
                'order' => $order++,
                'group' => 'Коммерческая'
            ];
        }

        // Земельные участки (только купить/продать)
        $landActions = [
            ['name' => 'Купить земельный участок', 'action' => 'Купить'],
            ['name' => 'Продать земельный участок', 'action' => 'Продать'],
        ];

        foreach ($landActions as $item) {
            $categories[] = [
                'name' => $item['name'],
                'description' => $item['name'],
                'icon' => 'tree',
                'action' => $item['action'],
                'order' => $order++,
                'group' => 'Земля'
            ];
        }

        // Создаем все категории
        foreach ($categories as $category) {
            NedvizhimostCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug('nedvizhimost-' . $category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'action' => $category['action'], // Одно конкретное действие
                'group' => $category['group'], // Группа для группировки в меню
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Создано ' . count($categories) . ' категорий недвижимости');
    }
}