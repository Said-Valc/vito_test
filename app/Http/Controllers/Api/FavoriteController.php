<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\AutoListing;
use App\Models\NedvizhimostListing;
use App\Models\ElektronikaListing;
use App\Models\HobbyListing;
use App\Models\RabotaVacancy;
use App\Models\RabotaResume;
use App\Models\UslugiListing;
use App\Models\LichnieVeschiListing;
use App\Models\DlyaDomaListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Получить избранные объявления пользователя
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $favorites = Favorite::where('user_id', $user->id)
            ->latest()
            ->get();
        
        // Преобразуем избранное в объявления
        $listings = $favorites->map(function ($favorite) {
            $listing = $this->findListing($favorite->listable_type, $favorite->listable_id);
            
            if (!$listing) {
                return null;
            }
            
            // Добавляем дополнительную информацию
            $listing->listing_type = $this->getListingTypeSlug($favorite->listable_type);
            $listing->favorite_id = $favorite->id;
            $listing->favorited_at = $favorite->created_at;
            
            // Добавляем URL изображения
            if ($listing->images && $listing->images->count() > 0) {
                $listing->main_image_url = $listing->images->first()->url;
            }
            
            return $listing;
        })->filter(); // Убираем null значения
        
        return response()->json([
            'favorites' => $favorites,
            'listings' => $listings->values(),
            'count' => $favorites->count(),
        ]);
    }
    
    /**
     * Добавить в избранное
     */
    public function store(Request $request)
    {
        $request->validate([
            'listable_type' => 'required|string',
            'listable_id' => 'required|integer',
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Получаем полное имя класса модели
        $listableType = $this->getModelClass($request->listable_type);
        
        if (!$listableType) {
            return response()->json(['error' => 'Invalid listing type'], 400);
        }
        
        // Проверяем, существует ли объявление
        $listing = $this->findListing($listableType, $request->listable_id);
        
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }
        
        // Проверяем, не добавлено ли уже в избранное
        $exists = Favorite::where('user_id', $user->id)
            ->where('listable_type', $listableType)
            ->where('listable_id', $request->listable_id)
            ->exists();
        
        if ($exists) {
            return response()->json(['message' => 'Already in favorites']);
        }
        
        // Добавляем в избранное
        $favorite = Favorite::create([
            'user_id' => $user->id,
            'listable_type' => $listableType,
            'listable_id' => $request->listable_id,
        ]);
        
        return response()->json([
            'message' => 'Added to favorites',
            'favorite' => $favorite,
        ], 201);
    }
    
    /**
     * Удалить из избранного
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'listable_type' => 'required|string',
            'listable_id' => 'required|integer',
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $listableType = $this->getModelClass($request->listable_type);
        
        if (!$listableType) {
            return response()->json(['error' => 'Invalid listing type'], 400);
        }
        
        $deleted = Favorite::where('user_id', $user->id)
            ->where('listable_type', $listableType)
            ->where('listable_id', $request->listable_id)
            ->delete();
        
        if ($deleted) {
            return response()->json(['message' => 'Removed from favorites']);
        }
        
        return response()->json(['error' => 'Favorite not found'], 404);
    }
    
    /**
     * Проверить, есть ли объявление в избранном
     */
    public function check(Request $request)
    {
        $request->validate([
            'listable_type' => 'required|string',
            'listable_id' => 'required|integer',
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['is_favorite' => false]);
        }
        
        $listableType = $this->getModelClass($request->listable_type);
        
        if (!$listableType) {
            return response()->json(['is_favorite' => false]);
        }
        
        $isFavorite = Favorite::where('user_id', $user->id)
            ->where('listable_type', $listableType)
            ->where('listable_id', $request->listable_id)
            ->exists();
        
        return response()->json(['is_favorite' => $isFavorite]);
    }
    
    /**
     * Переключить избранное (добавить/удалить)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'listable_type' => 'required|string',
            'listable_id' => 'required|integer',
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $listableType = $this->getModelClass($request->listable_type);
        
        if (!$listableType) {
            return response()->json(['error' => 'Invalid listing type'], 400);
        }
        
        // Проверяем существование объявления
        $listing = $this->findListing($listableType, $request->listable_id);
        
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }
        
        // Ищем существующую запись
        $favorite = Favorite::where('user_id', $user->id)
            ->where('listable_type', $listableType)
            ->where('listable_id', $request->listable_id)
            ->first();
        
        if ($favorite) {
            // Удаляем из избранного
            $favorite->delete();
            return response()->json([
                'message' => 'Removed from favorites',
                'is_favorite' => false,
            ]);
        } else {
            // Добавляем в избранное
            $favorite = Favorite::create([
                'user_id' => $user->id,
                'listable_type' => $listableType,
                'listable_id' => $request->listable_id,
            ]);
            return response()->json([
                'message' => 'Added to favorites',
                'is_favorite' => true,
                'favorite' => $favorite,
            ], 201);
        }
    }
    
    /**
     * Получить маппинг slug -> полное имя класса модели
     */
    private function getModelClass(string $type): ?string
    {
        $map = [
            'auto' => AutoListing::class,
            'nedvizhimost' => NedvizhimostListing::class,
            'elektronika' => ElektronikaListing::class,
            'hobby' => HobbyListing::class,
            'vacancy' => RabotaVacancy::class,
            'resume' => RabotaResume::class,
            'uslugi' => UslugiListing::class,
            'lichnie_veschi' => LichnieVeschiListing::class,
            'dlya_doma' => DlyaDomaListing::class,
            // Также поддерживаем полные имена классов
            'App\\Models\\AutoListing' => AutoListing::class,
            'App\\Models\\NedvizhimostListing' => NedvizhimostListing::class,
            'App\\Models\\ElektronikaListing' => ElektronikaListing::class,
            'App\\Models\\HobbyListing' => HobbyListing::class,
            'App\\Models\\RabotaVacancy' => RabotaVacancy::class,
            'App\\Models\\RabotaResume' => RabotaResume::class,
            'App\\Models\\UslugiListing' => UslugiListing::class,
            'App\\Models\\LichnieVeschiListing' => LichnieVeschiListing::class,
            'App\\Models\\DlyaDomaListing' => DlyaDomaListing::class,
        ];
        
        return $map[$type] ?? null;
    }
    
    /**
     * Получить slug типа объявления
     */
    private function getListingTypeSlug(string $fullClassName): string
    {
        $map = [
            AutoListing::class => 'auto',
            NedvizhimostListing::class => 'nedvizhimost',
            ElektronikaListing::class => 'elektronika',
            HobbyListing::class => 'hobby',
            RabotaVacancy::class => 'vacancy',
            RabotaResume::class => 'resume',
            UslugiListing::class => 'uslugi',
            LichnieVeschiListing::class => 'lichnie_veschi',
            DlyaDomaListing::class => 'dlya_doma',
        ];
        
        return $map[$fullClassName] ?? 'unknown';
    }
    
    /**
     * Найти объявление по типу и ID
     */
    private function findListing(string $type, int $id)
    {
        $modelClass = $type;
        
        // Если передан slug, получаем полное имя класса
        if (!class_exists($modelClass)) {
            $modelClass = $this->getModelClass($type);
        }
        
        if (!$modelClass) {
            return null;
        }
        
        return $modelClass::with(['user', 'images'])->find($id);
    }
}