<?php

namespace App\Http\Controllers;

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
use Inertia\Inertia;

class FavoritePageController extends Controller
{
    /**
     * Показать страницу с избранными объявлениями
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $favorites = Favorite::where('user_id', $user->id)
            ->with(['user'])
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
        })->filter()->values();
        
        return Inertia::render('Favorites/Index', [
            'listings' => $listings,
            'count' => $favorites->count(),
        ]);
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
        
        if (!class_exists($modelClass)) {
            return null;
        }
        
        return $modelClass::with(['user', 'images'])->find($id);
    }
}