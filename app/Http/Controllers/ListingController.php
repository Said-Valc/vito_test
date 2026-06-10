<?php

namespace App\Http\Controllers;

use App\Http\Middleware\NotSuspended;
use App\Models\AutoListing;
use App\Models\NedvizhimostListing;
use App\Models\ElektronikaListing;
use App\Models\HobbyListing;
use App\Models\RabotaVacancy;
use App\Models\RabotaResume;
use App\Models\UslugiListing;
use App\Models\LichnieVeschiListing;
use App\Models\DlyaDomaListing;
use App\Models\ListingImage;
use App\Models\AutoCategory;
use App\Models\NedvizhimostCategory;
use App\Models\ElektronikaCategory;
use App\Models\HobbyCategory;
use App\Models\RabotaCategory;
use App\Models\UslugiCategory;
use App\Models\LichnieVeschiCategory;
use App\Models\DlyaDomaCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ListingController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware(
                ['auth', 'verified', NotSuspended::class],
                except: ['index', 'show']
            )
        ];
    }

    /**
     * Display a listing of the resource (Главная страница со всеми объявлениями).
     */
    public function index(Request $request)
    {
        // Собираем объявления из всех таблиц
        $allListings = collect();
        
        // Авто
        $autoListings = AutoListing::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'auto';
                return $item;
            });
        
        // Недвижимость
        $nedvizhimostListings = NedvizhimostListing::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'nedvizhimost';
                return $item;
            });
        
        // Электроника
        $elektronikaListings = ElektronikaListing::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'elektronika';
                return $item;
            });
                
        // Хобби
        $hobbyListings = HobbyListing::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'hobby';
                return $item;
            });
        
        // Работа (вакансии)
        $vacancies = RabotaVacancy::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'vacancy';
                return $item;
            });
        
        // Работа (резюме)
        $resumes = RabotaResume::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('about_me', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'resume';
                return $item;
            });
        
        // Услуги
        $uslugiListings = UslugiListing::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'uslugi';
                return $item;
            });
        
        // Личные вещи
        $lichnieListings = LichnieVeschiListing::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'lichnie_veschi';
                return $item;
            });
        
        // Для дома
        $domaListings = DlyaDomaListing::with(['user', 'category', 'images'])
            ->where('is_active', true)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'dlya_doma';
                return $item;
            });
        
        // Объединяем все коллекции
        $allListings = $autoListings
            ->concat($nedvizhimostListings)
            ->concat($elektronikaListings)
            ->concat($hobbyListings)
            ->concat($vacancies)
            ->concat($resumes)
            ->concat($uslugiListings)
            ->concat($lichnieListings)
            ->concat($domaListings);
        
        // Сортируем по дате создания
        $allListings = $allListings->sortByDesc('created_at')->values();

        return Inertia::render('Home', [
            'listings' => $allListings,
            'searchTerm' => $request->search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Загружаем все категории для передачи в представление
        $categories = [
            'auto' => AutoCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
            'nedvizhimost' => NedvizhimostCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
            'elektronika' => ElektronikaCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
            'hobby' => HobbyCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
            'rabota' => RabotaCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
            'uslugi' => UslugiCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
            'lichnie_veschi' => LichnieVeschiCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
            'dlya_doma' => DlyaDomaCategory::where('is_active', true)->orderBy('order')->get(['id', 'name']),
        ];

        return Inertia::render('Listing/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Базовая валидация
        $validated = $request->validate([
            'category_type' => 'required|in:auto,nedvizhimost,elektronika,hobby,rabota,uslugi,lichnie_veschi,dlya_doma',
            'title' => 'required|max:255',
            'description' => 'required',
            'price' => 'nullable|numeric|min:0',
            'city' => 'required|max:100',
            'phone' => 'required|max:20',
            'images' => 'nullable|array',
            'images.*' => 'image|max:3072|mimes:jpeg,jpg,png,webp',
        ]);

        // Обработка category_id
        $categoryId = null;
        
        if ($request->has('category_id') && $request->category_id !== null && $request->category_id !== '') {
            // Если category_id = -1, это значит, что нужно создать заглушку
            if ($request->category_id == -1) {
                $categoryId = $this->createDefaultCategory($request->category_type);
            } else {
                // Проверяем существование категории
                $categoryExists = $this->checkCategoryExists(
                    $request->category_type, 
                    $request->category_id
                );
                
                if ($categoryExists) {
                    $categoryId = $request->category_id;
                } else {
                    // Если категория не существует, создаем заглушку
                    $categoryId = $this->createDefaultCategory($request->category_type);
                }
            }
        } else {
            // Если category_id не передан, но категория требует подкатегорию, создаем заглушку
            $categoriesWithRequiredSubcategory = ['auto', 'nedvizhimost', 'elektronika', 'rabota', 'uslugi'];
            if (in_array($request->category_type, $categoriesWithRequiredSubcategory)) {
                $categoryId = $this->createDefaultCategory($request->category_type);
            }
        }

        // Создаем объявление в соответствующей таблице
        $listing = $this->createListing($request, $categoryId);

        if (!$listing) {
            return back()->withErrors(['error' => 'Ошибка создания объявления']);
        }

        // Сохраняем изображения - используем полиморфную связь
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store($listing->getTable() . '/' . $listing->id, 'public');
                
                $listing->images()->create([
                    'path' => $path,
                    'position' => $index,
                    'is_main' => $index === 0,
                ]);
            }
        }

        return redirect()->route('dashboard')
            ->with('status', 'Объявление успешно создано!');
    }

    /**
     * Display the specified resource.
     */

    /**
     * Поиск объявлений рядом с указанными координатами
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:1|max:500', // км
        ]);
        
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 50; // по умолчанию 50 км
        
        $listings = collect();
        
        // Получаем объявления из всех таблиц
        $tables = [
            'auto' => AutoListing::class,
            'nedvizhimost' => NedvizhimostListing::class,
            'elektronika' => ElektronikaListing::class,
            'hobby' => HobbyListing::class,
            'uslugi' => UslugiListing::class,
            'lichnie_veschi' => LichnieVeschiListing::class,
            'dlya_doma' => DlyaDomaListing::class,
        ];
        
        foreach ($tables as $type => $model) {
            $items = $model::with(['user', 'images'])
                ->where('is_active', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select('*')
                ->selectRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$latitude, $longitude, $latitude]
                )
                ->having('distance', '<=', $radius)
                ->orderBy('distance')
                ->limit(20)
                ->get()
                ->map(function($item) use ($type) {
                    $item->listing_type = $type;
                    return $item;
                });
            
            $listings = $listings->concat($items);
        }
        
        // Сортируем по расстоянию
        $listings = $listings->sortBy('distance')->values();
        
        return response()->json([
            'data' => $listings,
            'center' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'radius' => $radius,
        ]);
    }
    public function show(Request $request, $type, $id)
    {
        $listing = $this->findListing($type, $id);
        
        if (!$listing) {
            abort(404);
        }

        // Загружаем изображения через полиморфную связь
        $images = ListingImage::where('listable_type', 'App\\Models\\' . $this->getModelName($type))
            ->where('listable_id', $id)
            ->orderBy('position')
            ->get()
            ->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'is_main' => $image->is_main,
                ];
            });

        return Inertia::render('Listing/Show', [
            'listing' => $listing,
            'type' => $type,
            'images' => $images,
            'user' => $listing->user->only(['name', 'id']),
            'canModify' => Auth::user() ? Auth::user()->id === $listing->user_id : false
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $type, $id)
    {
        $listing = $this->findListing($type, $id);
        
        if (!$listing) {
            abort(404);
        }

        Gate::authorize('modify', $listing);

        // Загружаем изображения для формы редактирования
        $images = ListingImage::where('listable_type', 'App\\Models\\' . $this->getModelName($type))
            ->where('listable_id', $id)
            ->orderBy('position')
            ->get()
            ->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'path' => $image->path,
                    'is_main' => $image->is_main,
                ];
            });

        return Inertia::render('Listing/Edit', [
            'listing' => $listing,
            'type' => $type,
            'existingImages' => $images
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $type, $id)
    {
        $listing = $this->findListing($type, $id);
        
        if (!$listing) {
            abort(404);
        }

        Gate::authorize('modify', $listing);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'price' => 'nullable|numeric',
            'city' => 'required|max:100',
            'phone' => 'required|max:20',
            'images' => 'nullable|array',
            'images.*' => 'image|max:3072|mimes:jpeg,jpg,png,webp',
        ]);

        // Обновляем объявление
        $listing->update($validated);

        // Если загружены новые изображения
        if ($request->hasFile('images')) {
            // Удаляем старые изображения
            $oldImages = ListingImage::where('listable_type', 'App\\Models\\' . $this->getModelName($type))
                ->where('listable_id', $id)
                ->get();
            
            foreach ($oldImages as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }

            // Сохраняем новые
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store($type . '/' . $id, 'public');
                
                ListingImage::create([
                    'listable_type' => 'App\\Models\\' . $this->getModelName($type),
                    'listable_id' => $id,
                    'path' => $path,
                    'position' => $index,
                    'is_main' => $index === 0,
                ]);
            }
        }

        return redirect()->route('dashboard')
            ->with('status', 'Объявление обновлено успешно!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $type, $id)
    {
        $listing = $this->findListing($type, $id);
        
        if (!$listing) {
            abort(404);
        }

        Gate::authorize('modify', $listing);

        // Удаляем изображения
        $images = ListingImage::where('listable_type', 'App\\Models\\' . $this->getModelName($type))
            ->where('listable_id', $id)
            ->get();
        
        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        // Удаляем объявление
        $listing->delete();

        return redirect()->route('dashboard')
            ->with('status', 'Объявление удалено успешно!');
    }

    /**
     * Создать дефолтную категорию-заглушку
     */
    private function createDefaultCategory($categoryType)
    {
        $modelClass = match($categoryType) {
            'auto' => AutoCategory::class,
            'nedvizhimost' => NedvizhimostCategory::class,
            'elektronika' => ElektronikaCategory::class,
            'hobby' => HobbyCategory::class,
            'rabota' => RabotaCategory::class,
            'uslugi' => UslugiCategory::class,
            'lichnie_veschi' => LichnieVeschiCategory::class,
            'dlya_doma' => DlyaDomaCategory::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        // Проверяем, есть ли уже категория-заглушка
        $defaultCategory = $modelClass::where('name', 'Другое')->first();
        
        if (!$defaultCategory) {
            // Создаем заглушку
            $defaultCategory = $modelClass::create([
                'name' => 'Другое',
                'order' => 999,
                'is_active' => true,
            ]);
        }
        
        return $defaultCategory->id;
    }

    /**
     * Создать объявление в соответствующей таблице
     */
    private function createListing(Request $request, $categoryId = null)
    {
        $baseData = [
            'user_id' => Auth::id(),
            'category_id' => $categoryId,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'city' => $request->city,
            'phone' => $request->phone,
            'is_active' => true,
            'latitude' => $request->latitude,      // ДОБАВИТЬ
            'longitude' => $request->longitude,    // ДОБАВИТЬ
            'location_id' => $request->location_id, // ДОБАВИТЬ
        ];

        switch ($request->category_type) {
            case 'auto':
                $request->validate([
                    'brand' => 'required|string',
                    'model' => 'required|string',
                    'year' => 'required|integer',
                ]);
                
                return AutoListing::create(array_merge($baseData, [
                    'brand' => $request->brand,
                    'model' => $request->model,
                    'year' => $request->year,
                    'mileage' => $request->mileage,
                    'fuel_type' => $request->fuel_type,
                    'transmission' => $request->transmission,
                    'color' => $request->color,
                ]));

            case 'nedvizhimost':
                $request->validate([
                    'action' => 'required|string',
                    'address' => 'required|string',
                ]);
                
                return NedvizhimostListing::create(array_merge($baseData, [
                    'action' => $request->action,
                    'rooms' => $request->rooms,
                    'area_total' => $request->area_total,
                    'floor' => $request->floor,
                    'floors_total' => $request->floors_total,
                    'address' => $request->address,
                ]));

            case 'elektronika':
                $request->validate([
                    'brand' => 'required|string',
                    'model' => 'required|string',
                    'condition' => 'required|string',
                ]);
                
                return ElektronikaListing::create(array_merge($baseData, [
                    'brand' => $request->brand,
                    'model' => $request->model,
                    'condition' => $request->condition,
                ]));

            case 'hobby':
                return HobbyListing::create($baseData);

            case 'rabota':
                if ($request->job_type === 'vacancy') {
                    $request->validate([
                        'company_name' => 'required|string',
                        'employment_type' => 'required|string',
                        'work_schedule' => 'required|string',
                    ]);
                    
                    return RabotaVacancy::create(array_merge($baseData, [
                        'company_name' => $request->company_name,
                        'employment_type' => $request->employment_type,
                        'work_schedule' => $request->work_schedule,
                        'salary_from' => $request->salary_from,
                        'salary_to' => $request->salary_to,
                    ]));
                } else {
                    $request->validate([
                        'full_name' => 'required|string',
                        'age' => 'nullable|integer',
                    ]);
                    
                    return RabotaResume::create(array_merge($baseData, [
                        'full_name' => $request->full_name,
                        'age' => $request->age,
                        'specialization' => $request->specialization,
                        'salary_expectation' => $request->salary_expectation,
                    ]));
                }

            case 'uslugi':
                $request->validate([
                    'service_type' => 'required|string',
                ]);
                
                return UslugiListing::create(array_merge($baseData, [
                    'service_type' => $request->service_type,
                    'price_type' => $request->price_type ?? 'За услугу',
                ]));

            case 'lichnie_veschi':
                return LichnieVeschiListing::create(array_merge($baseData, [
                    'condition' => $request->condition ?? 'Новое',
                ]));

            case 'dlya_doma':
                return DlyaDomaListing::create(array_merge($baseData, [
                    'condition' => $request->condition ?? 'Новое',
                ]));

            default:
                return null;
        }
    }

    /**
     * Найти объявление по типу и ID
     */
    private function findListing($type, $id)
    {
        return match($type) {
            'auto' => AutoListing::with(['user', 'category', 'images'])->find($id),
            'nedvizhimost' => NedvizhimostListing::with(['user', 'category', 'images'])->find($id),
            'elektronika' => ElektronikaListing::with(['user', 'category', 'images'])->find($id),
            'hobby' => HobbyListing::with(['user', 'category', 'images'])->find($id),
            'vacancy' => RabotaVacancy::with(['user', 'category', 'images'])->find($id),
            'resume' => RabotaResume::with(['user', 'category', 'images'])->find($id),
            'uslugi' => UslugiListing::with(['user', 'category', 'images'])->find($id),
            'lichnie_veschi' => LichnieVeschiListing::with(['user', 'category', 'images'])->find($id),
            'dlya_doma' => DlyaDomaListing::with(['user', 'category', 'images'])->find($id),
            default => null,
        };
    }

    /**
     * Проверить существование категории
     */
    private function checkCategoryExists($type, $categoryId)
    {
        $modelClass = match($type) {
            'auto' => AutoCategory::class,
            'nedvizhimost' => NedvizhimostCategory::class,
            'elektronika' => ElektronikaCategory::class,
            'hobby' => HobbyCategory::class,
            'rabota' => RabotaCategory::class,
            'uslugi' => UslugiCategory::class,
            'lichnie_veschi' => LichnieVeschiCategory::class,
            'dlya_doma' => DlyaDomaCategory::class,
            default => null,
        };

        if (!$modelClass) {
            return false;
        }

        return $modelClass::where('id', $categoryId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Получить имя модели для полиморфной связи
     */
    private function getModelName($type)
    {
        return match($type) {
            'auto' => 'AutoListing',
            'nedvizhimost' => 'NedvizhimostListing',
            'elektronika' => 'ElektronikaListing',
            'hobby' => 'HobbyListing',
            'vacancy' => 'RabotaVacancy',
            'resume' => 'RabotaResume',
            'uslugi' => 'UslugiListing',
            'lichnie_veschi' => 'LichnieVeschiListing',
            'dlya_doma' => 'DlyaDomaListing',
            default => ucfirst($type) . 'Listing',
        };
    }
}