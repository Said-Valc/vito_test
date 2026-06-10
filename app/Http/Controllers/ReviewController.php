<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function getRating(User $seller)
    {
        // Принудительно обновляем модель из БД
        $seller->refresh();
        
        $rating = $seller->rating;
        $count = $seller->rating_count;
        
        \Log::info('Getting rating', [
            'seller_id' => $seller->id,
            'rating' => $rating,
            'count' => $count
        ]);
        
        return response()->json([
            'rating' => $rating,
            'count' => $count,
            'distribution' => $seller->rating_distribution,
            'positive_percent' => $seller->positive_rating_percent,
        ]);
    }

    public function getReviews(Request $request, User $seller)
    {
        $reviews = $seller->receivedReviews()
            ->where('is_approved', true)
            ->with('buyer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $canReview = false;

        if (Auth::check() && Auth::id() !== $seller->id) {
            // Проверяем, есть ли завершенные сделки без отзыва
            $completedOrders = Order::where('buyer_id', Auth::id())
                ->where('seller_id', $seller->id)
                ->where('status', Order::STATUS_COMPLETED)
                ->get();
            
            foreach ($completedOrders as $order) {
                // Проверяем, не оставлен ли уже отзыв по этой сделке
                $hasReview = Review::where('buyer_id', Auth::id())
                    ->where('seller_id', $seller->id)
                    ->where('listable_type', $order->listable_type)
                    ->where('listable_id', $order->listable_id)
                    ->exists();
                
                if (!$hasReview) {
                    $canReview = true;
                    break;
                }
            }
        }

        return response()->json([
            'seller' => $seller->only(['id', 'name']),
            'reviews' => $reviews,
            'can_review' => $canReview,
        ]);
    }

    public function store(Request $request, User $seller)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'listable_type' => 'required|string',
            'listable_id' => 'required|integer',
        ]);

        if (Auth::id() === $seller->id) {
            return response()->json([
                'error' => 'Нельзя оставить отзыв самому себе'
            ], 403);
        }

        // Проверяем, был ли завершённый заказ именно по этому объявлению
        $order = Order::where('buyer_id', Auth::id())
            ->where('seller_id', $seller->id)
            ->where('listable_type', $request->listable_type)
            ->where('listable_id', $request->listable_id)
            ->where('status', Order::STATUS_COMPLETED)
            ->first();

        if (!$order) {
            return response()->json([
                'error' => 'Отзыв можно оставить только после завершённой сделки'
            ], 403);
        }

        // Проверка — не оставлен ли уже отзыв по ЭТОЙ сделке
        $exists = Review::where('buyer_id', Auth::id())
            ->where('seller_id', $seller->id)
            ->where('listable_type', $request->listable_type)
            ->where('listable_id', $request->listable_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Вы уже оставили отзыв по этой сделке'
            ], 403);
        }

        $review = Review::create([
            'seller_id' => $seller->id,
            'buyer_id' => Auth::id(),
            'listable_type' => $request->listable_type,
            'listable_id' => $request->listable_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified' => true,
            'is_approved' => true,
        ]);

        // ЯВНО пересчитываем рейтинг продавца
        $seller->refresh(); // Загружаем свежие данные из БД
        
        // Получаем свежий рейтинг
        $rating = $seller->rating;
        $ratingCount = $seller->rating_count;

        \Log::info('Review stored', [
            'seller_id' => $seller->id,
            'rating' => $rating,
            'count' => $ratingCount
        ]);

        return response()->json([
            'review' => $review->load('buyer'),
            'seller_rating' => $rating,
            'seller_rating_count' => $ratingCount,
        ], 201);
    }

    public function update(Request $request, Review $review)
    {
        if ($review->buyer_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_edited' => true,
        ]);

        return response()->json($review->load('buyer'));
    }

    public function destroy(Review $review)
    {
        if ($review->buyer_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['success' => true]);
    }

    public function respond(Request $request, Review $review)
    {
        if ($review->seller_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $review->update([
            'seller_response' => $request->response,
            'responded_at' => now(),
        ]);

        return response()->json($review);
    }
}