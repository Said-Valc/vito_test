<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Поиск локаций по названию (автодополнение)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $country = $request->get('country', '');
        
        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }
        
        $locationsQuery = Location::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                ->orWhere('region', 'like', "%{$query}%");
            });
        
        // Фильтр по стране
        if ($country) {
            $locationsQuery->where('country', $country);
        }
        
        $locations = $locationsQuery
            ->orderBy('population', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'data' => $locations->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'region' => $location->region,
                    'country' => $location->country,
                    'full_name' => $location->name . ($location->region ? ', ' . $location->region : ''),
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                ];
            })
        ]);
    }

    /**
     * Найти ближайшие локации по координатам
     */
    public function nearest(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'country' => 'nullable|string',
        ]);
        
        $query = Location::findNearest(
            $request->latitude,
            $request->longitude,
            5
        );
        
        // Фильтр по стране (опционально)
        if ($request->country) {
            $query = $query->filter(function ($location) use ($request) {
                return $location->country === $request->country;
            });
        }
        
        return response()->json([
            'data' => $query->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'region' => $location->region,
                    'country' => $location->country,
                    'full_name' => $location->name . ($location->region ? ', ' . $location->region : ''),
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'distance' => round($location->distance, 1),
                ];
            })->values()
        ]);
    }
    
    /**
     * Определить локацию по IP (для автоматического определения)
     */
    public function detectByIp(Request $request)
    {
        // В реальном проекте используйте сервис геолокации по IP
        // Например, ipapi.co, ipstack.com или бесплатный ip-api.com
        
        $ip = $request->ip();
        
        // Для локальной разработки возвращаем заглушку
        // В production используйте:
        // $response = file_get_contents("http://ip-api.com/json/{$ip}");
        // $data = json_decode($response, true);
        
        // Заглушка (Москва)
        $defaultLocation = Location::where('name', 'Москва')
            ->where('is_active', true)
            ->first();
        
        if (!$defaultLocation) {
            // Если нет Москвы, берем первый город
            $defaultLocation = Location::where('is_active', true)->first();
        }
        
        if (!$defaultLocation) {
            return response()->json(['data' => null]);
        }
        
        return response()->json([
            'data' => [
                'id' => $defaultLocation->id,
                'name' => $defaultLocation->name,
                'region' => $defaultLocation->region,
                'country' => $defaultLocation->country,
                'full_name' => $defaultLocation->name . ($defaultLocation->region ? ', ' . $defaultLocation->region : ''),
                'latitude' => $defaultLocation->latitude,
                'longitude' => $defaultLocation->longitude,
                'detected_by' => 'default',
            ]
        ]);
    }
}