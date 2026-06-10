<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoCategory;
use App\Models\NedvizhimostCategory;
use App\Models\RabotaCategory;
use App\Models\UslugiCategory;
use App\Models\LichnieVeschiCategory;
use App\Models\DlyaDomaCategory;
use App\Models\ElektronikaCategory;
use App\Models\HobbyCategory;

class CategoryController extends Controller
{
    public function index()
    {
        // Авто
        $auto = AutoCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug'])
            ->toArray();
        
        // Недвижимость - декодируем JSON строку в массив
        $nedvizhimost = NedvizhimostCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug', 'available_actions as action'])
            ->map(function($item) {
                if ($item->action && is_string($item->action)) {
                    $item->action = json_decode($item->action, true);
                }
                return $item;
            })
            ->toArray();
        
        // Работа
        $rabota = RabotaCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug', 'type as action'])
            ->toArray();
        
        // Услуги
        $uslugi = UslugiCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug'])
            ->toArray();
        
        // Личные вещи
        $lichnie_veschi = LichnieVeschiCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug'])
            ->toArray();
        
        // Для дома
        $dlya_doma = DlyaDomaCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug'])
            ->toArray();
        
        // Электроника
        $elektronika = ElektronikaCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug'])
            ->toArray();
        
        // Хобби
        $hobby = HobbyCategory::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug'])
            ->toArray();
        
        return response()->json([
            'auto' => $auto,
            'nedvizhimost' => $nedvizhimost,
            'rabota' => $rabota,
            'uslugi' => $uslugi,
            'lichnie_veschi' => $lichnie_veschi,
            'dlya_doma' => $dlya_doma,
            'elektronika' => $elektronika,
            'hobby' => $hobby,
        ]);
    }
}