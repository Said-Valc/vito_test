<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryPageController;
use App\Events\TypingEvent;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoritePageController;

// User Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::put('/profile', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    
    Route::get('/favorites', [FavoritePageController::class, 'index'])->name('favorites.index');


    // Профиль продавца
    Route::get('/seller/{user}', [ProfileController::class, 'sellerProfile'])->name('seller.profile');

    // Чат - чистые маршруты без дублей
    Route::get('/chats', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chats/{user}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/conversations', [ChatController::class, 'conversations'])->name('chat.conversations');
    Route::get('/chat/messages/{recipient}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send/{recipient}', [ChatController::class, 'store'])->name('chat.send');
    Route::post('/chat/mark-as-read/{sender}', [ChatController::class, 'markAsRead'])->name('chat.mark-as-read');
    Route::get('/chat/online-status/{user}', [ChatController::class, 'onlineStatus'])->name('chat.online-status');
    Route::post('/chat/update-online-status', [ChatController::class, 'updateOnlineStatus'])->name('chat.update-online');

    // Заказы
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::post('/{order}/activate', [OrderController::class, 'activate'])->name('orders.activate');
        Route::post('/{order}/confirm-buyer', [OrderController::class, 'confirmByBuyer'])->name('orders.confirm-buyer');
        Route::post('/{order}/confirm-seller', [OrderController::class, 'confirmBySeller'])->name('orders.confirm-seller');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/{order}/can-review', [OrderController::class, 'canReview'])->name('orders.can-review');
    });
});
Route::get('/nearby', [ListingController::class, 'nearby'])->name('listing.nearby');

// Listings Routes
Route::get('/api/unread-messages-count', function () {
    return response()->json([
        'count' => \App\Models\Message::where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->count()
    ]);
})->middleware('auth');

Route::get('/', [ListingController::class, 'index'])->name('home');
Route::get('/listing/create', [ListingController::class, 'create'])->name('listing.create');
Route::post('/listing', [ListingController::class, 'store'])->name('listing.store');
Route::get('/listing/{type}/{id}', [ListingController::class, 'show'])->name('listing.show');
Route::get('/listing/{type}/{id}/edit', [ListingController::class, 'edit'])->name('listing.edit');
Route::put('/listing/{type}/{id}', [ListingController::class, 'update'])->name('listing.update');
Route::delete('/listing/{type}/{id}', [ListingController::class, 'destroy'])->name('listing.destroy');

Route::get('/category/{type}', [CategoryPageController::class, 'showCategory'])->name('category.list');
Route::get('/category/{type}/{id}', [CategoryPageController::class, 'showSubcategory'])->name('category.show');

// Admin Routes
Route::middleware(['auth', 'verified', Admin::class])
    ->controller(AdminController::class)
    ->group(function () {
        Route::get('/admin', 'index')->name('admin.index');
        Route::get('/users/{user}', 'show')->name('user.show');
        Route::put('/admin/{user}/role', 'role')->name('admin.role');
        Route::put('/listing/{listing}/approve', 'approve')->name('admin.approve');
    });

// Auth Routes
require __DIR__ . '/auth.php';