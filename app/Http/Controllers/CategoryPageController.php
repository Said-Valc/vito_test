<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AutoListing;
use App\Models\NedvizhimostListing;
use App\Models\RabotaVacancy;
use App\Models\RabotaResume;
use App\Models\UslugiListing;
use App\Models\LichnieVeschiListing;
use App\Models\DlyaDomaListing;
use App\Models\ElektronikaListing;
use App\Models\HobbyListing;
use App\Models\AutoCategory;
use App\Models\NedvizhimostCategory;
use App\Models\RabotaCategory;
use App\Models\UslugiCategory;
use App\Models\LichnieVeschiCategory;
use App\Models\DlyaDomaCategory;
use App\Models\ElektronikaCategory;
use App\Models\HobbyCategory;

class CategoryPageController extends Controller
{
    // ВСЕ ОБЪЯВЛЕНИЯ КАТЕГОРИИ (например, все Авто)
    public function showCategory($type)
    {
        $categoryName = $this->getCategoryName($type);
        $subcategories = $this->getSubcategories($type);
        $listings = $this->getAllListingsByType($type);
       
        return Inertia::render('Category/Index', [
            'type' => $type,
            'categoryName' => $categoryName,
            'listings' => $listings,
            'subcategories' => $subcategories
        ]);
    }

    // КОНКРЕТНАЯ ПОДКАТЕГОРИЯ (например, Грузовые)
    public function showSubcategory(Request $request, $type, $id)
    {
        $subcategory = $this->getSubcategoryById($type, $id);
        $categoryName = $this->getCategoryName($type);
        
        // Получаем все подкатегории для сайдбара
        $subcategories = $this->getSubcategories($type);
        
        // Получаем объявления только этой подкатегории
        $listings = $this->getListingsBySubcategory($type, $id, $request);

        return Inertia::render('Category/Subcategory', [
            'type' => $type,
            'categoryName' => $categoryName,
            'subcategory' => $subcategory,
            'subcategories' => $subcategories, // Передаем все подкатегории
            'listings' => $listings,
        ]);
    }

    private function getCategoryName($type)
    {
        $names = [
            'auto' => 'Авто',
            'nedvizhimost' => 'Недвижимость',
            'rabota' => 'Работа',
            'uslugi' => 'Услуги',
            'lichnie_veschi' => 'Личные вещи',
            'dlya_doma' => 'Для дома',
            'elektronika' => 'Электроника',
            'hobby' => 'Хобби и отдых'
        ];

        return $names[$type] ?? 'Категория';
    }

    // ПОЛУЧИТЬ ВСЕ ОБЪЯВЛЕНИЯ КАТЕГОРИИ
    private function getAllListingsByType($type)
    {
        switch($type) {
            case 'auto':
                return AutoListing::with('category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'nedvizhimost':
                return NedvizhimostListing::with('category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'rabota':
                return [
                    'vacancies' => RabotaVacancy::with('category')
                        ->where('is_active', true)
                        ->latest()
                        ->paginate(15),
                    'resumes' => RabotaResume::with('category')
                        ->where('is_active', true)
                        ->latest()
                        ->paginate(15)
                ];
                
            case 'uslugi':
                return UslugiListing::with('category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'lichnie_veschi':
                return LichnieVeschiListing::with('category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'dlya_doma':
                return DlyaDomaListing::with('category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'elektronika':
                return ElektronikaListing::with('category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'hobby':
                return HobbyListing::with('category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            default:
                return [];
        }
    }

    // ПОЛУЧИТЬ ОБЪЯВЛЕНИЯ ПОДКАТЕГОРИИ
    private function getListingsBySubcategory($type, $id)
    {
        switch($type) {
            case 'auto':
                return AutoListing::where('category_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'nedvizhimost':
                return NedvizhimostListing::where('category_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'rabota':
                return [
                    'vacancies' => RabotaVacancy::where('category_id', $id)
                        ->where('is_active', true)
                        ->latest()
                        ->paginate(15),
                    'resumes' => RabotaResume::where('category_id', $id)
                        ->where('is_active', true)
                        ->latest()
                        ->paginate(15)
                ];
                
            case 'uslugi':
                return UslugiListing::where('category_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'lichnie_veschi':
                return LichnieVeschiListing::where('category_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'dlya_doma':
                return DlyaDomaListing::where('category_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'elektronika':
                return ElektronikaListing::where('category_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            case 'hobby':
                return HobbyListing::where('category_id', $id)
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
                    
            default:
                return [];
        }
    }

    private function getSubcategories($type)
    {
        switch($type) {
            case 'auto':
                return AutoCategory::where('is_active', true)->orderBy('order')->get();
            case 'nedvizhimost':
                return NedvizhimostCategory::where('is_active', true)->orderBy('order')->get();
            case 'rabota':
                return RabotaCategory::where('is_active', true)->orderBy('order')->get();
            case 'uslugi':
                return UslugiCategory::where('is_active', true)->orderBy('order')->get();
            case 'lichnie_veschi':
                return LichnieVeschiCategory::where('is_active', true)->orderBy('order')->get();
            case 'dlya_doma':
                return DlyaDomaCategory::where('is_active', true)->orderBy('order')->get();
            case 'elektronika':
                return ElektronikaCategory::where('is_active', true)->orderBy('order')->get();
            case 'hobby':
                return HobbyCategory::where('is_active', true)->orderBy('order')->get();
            default:
                return [];
        }
    }

    private function getSubcategoryById($type, $id)
    {
        switch($type) {
            case 'auto':
                return AutoCategory::findOrFail($id);
            case 'nedvizhimost':
                return NedvizhimostCategory::findOrFail($id);
            case 'rabota':
                return RabotaCategory::findOrFail($id);
            case 'uslugi':
                return UslugiCategory::findOrFail($id);
            case 'lichnie_veschi':
                return LichnieVeschiCategory::findOrFail($id);
            case 'dlya_doma':
                return DlyaDomaCategory::findOrFail($id);
            case 'elektronika':
                return ElektronikaCategory::findOrFail($id);
            case 'hobby':
                return HobbyCategory::findOrFail($id);
            default:
                abort(404);
        }
    }
}