<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $listings = null;
        
        if ($request->user()->role !== 'suspended') {
            // Получаем все объявления пользователя из разных таблиц
            $listings = collect();
            
            // Загружаем авто
            $autoListings = \App\Models\AutoListing::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'auto';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем недвижимость
            $nedvizhimostListings = \App\Models\NedvizhimostListing::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'nedvizhimost';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем электронику
            $elektronikaListings = \App\Models\ElektronikaListing::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'elektronika';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем хобби
            $hobbyListings = \App\Models\HobbyListing::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'hobby';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем работу (вакансии)
            $vacancies = \App\Models\RabotaVacancy::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'vacancy';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем работу (резюме)
            $resumes = \App\Models\RabotaResume::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'resume';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем услуги
            $uslugiListings = \App\Models\UslugiListing::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'uslugi';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем личные вещи
            $lichnieListings = \App\Models\LichnieVeschiListing::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'lichnie_veschi';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Загружаем для дома
            $domaListings = \App\Models\DlyaDomaListing::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(function ($item) {
                    $item->listing_type = 'dlya_doma';
                    $item->images_urls = $item->images()->get()->map(function($img) {
                        return '/storage/' . $img->path;
                    });
                    return $item;
                });
            
            // Объединяем все
            $listings = $autoListings
                ->concat($nedvizhimostListings)
                ->concat($elektronikaListings)
                ->concat($hobbyListings)
                ->concat($vacancies)
                ->concat($resumes)
                ->concat($uslugiListings)
                ->concat($lichnieListings)
                ->concat($domaListings)
                ->sortByDesc('created_at')
                ->values();
            
            // Пагинация
            $page = $request->get('page', 1);
            $perPage = 10;
            $total = $listings->count();
            $paginatedListings = new \Illuminate\Pagination\LengthAwarePaginator(
                $listings->forPage($page, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            $listings = $paginatedListings;
        }

        return Inertia::render('Dashboard', [
            'listings' => $listings,
            'status' => session('status')
        ]);
    }
}