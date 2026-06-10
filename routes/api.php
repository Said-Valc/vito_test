<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\LocationController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\ListingController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->group(function () {
    // Публичные маршруты
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Защищенные маршруты
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// ПУБЛИЧНЫЕ маршруты (не требуют авторизации)
Route::prefix('v1')->group(function () {
    // Локации (города) - доступны всем
    Route::get('/locations/search', [LocationController::class, 'search'])->name('api.locations.search');
    Route::get('/locations/nearest', [LocationController::class, 'nearest'])->name('api.locations.nearest');
    Route::get('/locations/detect', [LocationController::class, 'detectByIp'])->name('api.locations.detect');
    
    // Получить рейтинг продавца (доступно всем)
    Route::get('/sellers/{seller}/rating', [ReviewController::class, 'getRating'])
        ->name('api.seller.rating');
    
    // Получить отзывы о продавце (доступно всем)
    Route::get('/sellers/{seller}/reviews', [ReviewController::class, 'getReviews'])
        ->name('api.seller.reviews');
    Route::get('/listings', [ListingController::class, 'index']);

    Route::get('/locations/search', [LocationController::class, 'search']);
    // Получить информацию об объявлении (доступно всем)
    Route::get('/listings/{type}/{id}', function ($type, $id) {
        $modelClass = match($type) {
            'auto' => \App\Models\AutoListing::class,
            'nedvizhimost' => \App\Models\NedvizhimostListing::class,
            'elektronika' => \App\Models\ElektronikaListing::class,
            'hobby' => \App\Models\HobbyListing::class,
            'vacancy' => \App\Models\RabotaVacancy::class,
            'resume' => \App\Models\RabotaResume::class,
            'uslugi' => \App\Models\UslugiListing::class,
            'lichnie_veschi' => \App\Models\LichnieVeschiListing::class,
            'dlya_doma' => \App\Models\DlyaDomaListing::class,
            default => null,
        };
        
        if (!$modelClass) {
            return response()->json(['error' => 'Invalid type'], 400);
        }
        
        $listing = $modelClass::with(['user', 'images'])->find($id);
        
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }
        
        // Добавляем тип объявления
        $listing->listing_type = $type;
        
        // Добавляем URL главного изображения
        if ($listing->images && $listing->images->count() > 0) {
            $listing->main_image_url = $listing->images->first()->url;
        }
        
        return response()->json($listing);
    })->name('api.listing.show');
});

Route::get('/categories', [CategoryController::class, 'index']);

// ЗАЩИЩЕННЫЕ маршруты (требуют авторизацию)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Создать отзыв
    Route::post('/sellers/{seller}/reviews', [ReviewController::class, 'store'])
        ->name('api.reviews.store');
    
    // Обновить отзыв
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
        ->name('api.reviews.update');
    
    // Удалить отзыв
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('api.reviews.destroy');
    
    // Ответить на отзыв
    Route::post('/reviews/{review}/respond', [ReviewController::class, 'respond'])
        ->name('api.reviews.respond');
    
    // Получить объявления продавца
    Route::get('/sellers/{user}/listings', [App\Http\Controllers\ProfileController::class, 'getSellerListings'])
        ->name('api.seller.listings');
    
    // Избранное
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('api.favorites.index');
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('api.favorites.toggle');
    Route::post('/favorites', [FavoriteController::class, 'store'])->name('api.favorites.store');
    Route::delete('/favorites', [FavoriteController::class, 'destroy'])->name('api.favorites.destroy');
    Route::get('/favorites/check', [FavoriteController::class, 'check'])->name('api.favorites.check');
    
    // Проверка существующего заказа
    Route::get('/orders/check', function (Request $request) {
        $request->validate([
            'listable_type' => 'required|string',
            'listable_id' => 'required|integer',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'order' => null
            ]);
        }

        // НОРМАЛИЗАЦИЯ listable_type
        $typeMap = [
            'App\\Models\\AutoListing' => \App\Models\AutoListing::class,
            'auto' => \App\Models\AutoListing::class,

            'App\\Models\\NedvizhimostListing' => \App\Models\NedvizhimostListing::class,
            'nedvizhimost' => \App\Models\NedvizhimostListing::class,

            'App\\Models\\ElektronikaListing' => \App\Models\ElektronikaListing::class,
            'elektronika' => \App\Models\ElektronikaListing::class,
        ];

        $listableType = $typeMap[$request->listable_type] ?? $request->listable_type;

        try {
            $order = \App\Models\Order::where(function ($q) {
                    $q->where('buyer_id', auth()->id())
                      ->orWhere('seller_id', auth()->id());
                })
                ->where('listable_type', $listableType)
                ->where('listable_id', $request->listable_id)
                ->whereIn('status', [
                    \App\Models\Order::STATUS_PENDING,
                    \App\Models\Order::STATUS_ACTIVE
                ])
                ->first();

            return response()->json([
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    // Получить заказы пользователя
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('api.orders.index');
    
    // Создать заказ
    Route::post('/orders', [OrderController::class, 'store'])
        ->name('api.orders.store');
    
    // Активировать заказ
    Route::post('/orders/{order}/activate', [OrderController::class, 'activate'])
        ->name('api.orders.activate');
    
    // Подтверждение покупателем
    Route::post('/orders/{order}/confirm-buyer', [OrderController::class, 'confirmByBuyer'])
        ->name('api.orders.confirm-buyer');
    
    // Подтверждение продавцом
    Route::post('/orders/{order}/confirm-seller', [OrderController::class, 'confirmBySeller'])
        ->name('api.orders.confirm-seller');
    
    // Отменить заказ
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->name('api.orders.cancel');
    
    // Проверить возможность оставить отзыв
    Route::get('/orders/{order}/can-review', [OrderController::class, 'canReview'])
        ->name('api.orders.can-review');
});

// WebSocket маршруты (не требуют CSRF)
Route::post('/chat/{user}/typing', function ($userId) {
    broadcast(new \App\Events\TypingEvent(auth()->id(), $userId))->toOthers();
    return response()->json(['success' => true]);
})->middleware('auth')->name('chat.typing');

// Получение непрочитанных сообщений
Route::get('/unread-messages-count', function () {
    return response()->json([
        'count' => \App\Models\Message::where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->count()
    ]);
})->middleware('auth')->name('unread-messages-count');

// Получение текущего пользователя
Route::get('/user', function () {
    return response()->json(auth()->user());
})->middleware('auth')->name('api.user');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
});
Route::middleware('auth:sanctum')
    ->prefix('v1')
    ->group(function () {

        Route::post(
            '/listings',
            [ListingController::class, 'store']
        );

    });