<?php

namespace App\Http\Controllers;

use App\Models\User;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Показ профиля (объединённая страница с публичной информацией и настройками)
     */
    public function show(User $user)
    {
        // Получаем все объявления продавца
        $listings = $this->getUserListings($user->id);
        
        // Разделяем на активные и завершенные
        $activeListings = $listings->filter(function($listing) {
            return $listing->is_active == true;
        })->values();
        
        $completedListings = $listings->filter(function($listing) {
            return $listing->is_active == false;
        })->values();
        
        // Загружаем рейтинг и количество отзывов продавца
        $seller = $user->loadCount(['reviews as rating_avg' => function($query) {
            $query->select(\DB::raw('coalesce(avg(rating), 0)'));
        }]);
        
        // Получаем фактический рейтинг
        $rating = $user->reviews()->avg('rating') ?? 0;
        $ratingCount = $user->reviews()->count();
        
        // Добавляем рейтинг к продавцу
        $seller->rating = round($rating, 1);
        $seller->rating_count = $ratingCount;
        
        // Если пользователь смотрит СВОЙ профиль, передаём данные для форм
        $userData = null;
        $status = null;
        
        if (Auth::check() && Auth::id() === $user->id) {
            $userData = Auth::user();
            $status = session('status');
        }
        
        return Inertia::render('Profile/Index', [
            'user' => $userData,           // Только для своего профиля (данные для форм)
            'seller' => $seller,           // Всегда передаём данные продавца с рейтингом
            'activeListings' => $activeListings,
            'completedListings' => $completedListings,
            'completedCount' => $completedListings->count(),
            'status' => $status,
        ]);
    }

    /**
     * Страница редактирования (редирект на show)
     */
    public function edit(Request $request)
    {
        return redirect()->route('profile.show', $request->user());
    }

    /**
     * Страница профиля продавца (редирект на show)
     */
    public function sellerProfile(User $user)
    {
        return redirect()->route('profile.show', $user);
    }

    /**
     * API метод для получения объявлений продавца (для Infinite Scroll)
     */
    public function getSellerListings(Request $request, User $user)
    {
        $type = $request->get('type', 'active');
        $perPage = $request->get('per_page', 12);
        
        $listings = $this->getUserListings($user->id);
        
        if ($type === 'active') {
            $listings = $listings->filter(fn($l) => $l->is_active == true);
        } else {
            $listings = $listings->filter(fn($l) => $l->is_active == false);
        }
        
        // Пагинация
        $currentPage = $request->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $listings->forPage($currentPage, $perPage),
            $listings->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return response()->json($paginated);
    }

    /**
     * Получить все объявления пользователя из всех таблиц
     */
    private function getUserListings($userId)
    {
        $allListings = collect();
        
        // Авто
        $autoListings = AutoListing::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'auto';
                return $item;
            });
        
        // Недвижимость
        $nedvizhimostListings = NedvizhimostListing::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'nedvizhimost';
                return $item;
            });
        
        // Электроника
        $elektronikaListings = ElektronikaListing::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'elektronika';
                return $item;
            });
        
        // Хобби
        $hobbyListings = HobbyListing::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'hobby';
                return $item;
            });
        
        // Работа (вакансии)
        $vacancies = RabotaVacancy::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'vacancy';
                return $item;
            });
        
        // Работа (резюме)
        $resumes = RabotaResume::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'resume';
                return $item;
            });
        
        // Услуги
        $uslugiListings = UslugiListing::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'uslugi';
                return $item;
            });
        
        // Личные вещи
        $lichnieListings = LichnieVeschiListing::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'lichnie_veschi';
                return $item;
            });
        
        // Для дома
        $domaListings = DlyaDomaListing::with(['user', 'category', 'images'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function($item) {
                $item->listing_type = 'dlya_doma';
                return $item;
            });
        
        return $autoListings
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
    }

    public function updateInfo(Request $request)
    {
        $fields = $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)]
        ]);

        $request->user()->fill($fields);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('profile.show', $request->user())
            ->with('status', 'Профиль успешно обновлён!');
    }

    public function updatePassword(Request $request)
    {
        $fields = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:3']
        ]);

        $request->user()->update([
            'password' => Hash::make($fields['password'])
        ]);

        return redirect()->route('profile.show', $request->user())
            ->with('status', 'Пароль успешно изменён!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password']
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}