<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Создать новый заказ (после договоренности в чате)
     */
    public function store(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id',
            'listable_type' => 'required|string',
            'listable_id' => 'required|integer',
            'amount' => 'nullable|numeric|min:0',
        ]);

        // Проверяем, не существует ли уже активного заказа
        $existingOrder = Order::where('buyer_id', Auth::id())
            ->where('seller_id', $request->seller_id)
            ->where('listable_type', $request->listable_type)
            ->where('listable_id', $request->listable_id)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_ACTIVE])
            ->first();

        if ($existingOrder) {
            return response()->json([
                'error' => 'Активный заказ по этому объявлению уже существует'
            ], 422);
        }

        $order = Order::create([
            'buyer_id' => Auth::id(),
            'seller_id' => $request->seller_id,
            'listable_type' => $request->listable_type,
            'listable_id' => $request->listable_id,
            'amount' => $request->amount,
            'status' => Order::STATUS_PENDING,
        ]);

        // Загружаем связи для уведомления
        $order->load(['buyer', 'seller', 'listable']);
        
        // Отправляем уведомление продавцу о новой сделке
        $order->seller->notify(new NewOrderNotification($order));
        
        // Отправляем уведомление покупателю о создании сделки
        Auth::user()->notify(new OrderStatusChanged($order, 'created'));

        return response()->json($order, 201);
    }

    /**
     * Получить список заказов пользователя
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $role = $request->get('role', 'all');
        $status = $request->get('status', 'all');
        
        $query = Order::with(['buyer', 'seller', 'listable', 'listable.images']);
        
        if ($role === 'buyer') {
            $query->where('buyer_id', $userId);
        } elseif ($role === 'seller') {
            $query->where('seller_id', $userId);
        } else {
            $query->where(function($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            });
        }
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($order) {
                // Добавляем URL главного изображения для каждого объявления
                if ($order->listable && $order->listable->images) {
                    $mainImage = $order->listable->images->first();
                    $order->listable->main_image_url = $mainImage ? $mainImage->url : null;
                }
                return $order;
            });
        
        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'currentRole' => $role,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Покупатель подтверждает получение товара
     * Только после того, как продавец подтвердил отправку
     */
    public function confirmByBuyer(Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['error' => 'Только покупатель может подтвердить получение'], 403);
        }
        
        if ($order->status !== Order::STATUS_ACTIVE) {
            return response()->json(['error' => 'Неверный статус заказа'], 422);
        }
        
        // Проверяем, что продавец уже подтвердил отправку
        if (!$order->seller_confirmed_at) {
            return response()->json(['error' => 'Продавец еще не подтвердил отправку'], 422);
        }
        
        // Проверяем, что покупатель еще не подтверждал
        if ($order->buyer_confirmed_at) {
            return response()->json(['error' => 'Вы уже подтвердили получение'], 422);
        }
        
        $order->buyer_confirmed_at = now();
        $order->save();
        
        $completed = $order->complete();
        
        // Загружаем связи для уведомления
        $order->load(['buyer', 'seller', 'listable']);
        
        if ($completed) {
            // Уведомляем обе стороны о завершении сделки
            $order->buyer->notify(new OrderStatusChanged($order, 'completed_buyer'));
            $order->seller->notify(new OrderStatusChanged($order, 'completed_seller'));
        } else {
            // Уведомляем продавца о подтверждении покупателем
            $order->seller->notify(new OrderStatusChanged($order, 'buyer_confirmed'));
        }
        
        return response()->json([
            'success' => true,
            'completed' => $completed,
            'order' => $order
        ]);
    }

    /**
     * Продавец подтверждает отправку/готовность
     * Только после активации сделки
     */
    public function confirmBySeller(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json(['error' => 'Только продавец может подтвердить отправку'], 403);
        }
        
        if ($order->status !== Order::STATUS_ACTIVE) {
            return response()->json(['error' => 'Неверный статус заказа'], 422);
        }
        
        // Проверяем, что продавец еще не подтверждал
        if ($order->seller_confirmed_at) {
            return response()->json(['error' => 'Вы уже подтвердили отправку'], 422);
        }
        
        $order->seller_confirmed_at = now();
        $order->save();
        
        // Загружаем связи для уведомления
        $order->load(['buyer', 'seller', 'listable']);
        
        // Уведомляем покупателя о подтверждении продавцом
        $order->buyer->notify(new OrderStatusChanged($order, 'seller_confirmed'));
        
        return response()->json([
            'success' => true,
            'completed' => false,
            'message' => 'Вы подтвердили отправку. Покупатель должен подтвердить получение.',
            'order' => $order
        ]);
    }

    /**
     * Активировать заказ
     * ТОЛЬКО ПРОДАВЕЦ может активировать сделку
     */
    public function activate(Order $order)
    {
        // Только продавец может активировать сделку
        if ($order->seller_id !== Auth::id()) {
            return response()->json(['error' => 'Только продавец может начать сделку'], 403);
        }
        
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['error' => 'Неверный статус заказа. Сделка уже активирована или завершена.'], 422);
        }
        
        $order->status = Order::STATUS_ACTIVE;
        $order->save();
        
        // Загружаем связи для уведомления
        $order->load(['buyer', 'seller', 'listable']);
        
        // Уведомляем покупателя об активации сделки продавцом
        $order->buyer->notify(new OrderStatusChanged($order, 'activated', 'seller'));
        
        return response()->json([
            'success' => true, 
            'message' => 'Сделка активирована. Теперь вы можете подтвердить отправку.',
            'order' => $order
        ]);
    }

    /**
     * Отменить заказ
     */
    public function cancel(Order $order, Request $request)
    {
        if ($order->buyer_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_ACTIVE])) {
            return response()->json(['error' => 'Нельзя отменить завершенный заказ'], 422);
        }
        
        $order->status = Order::STATUS_CANCELLED;
        $order->cancelled_at = now();
        
        $cancelledBy = Auth::id() === $order->buyer_id ? 'buyer' : 'seller';
        
        if ($request->has('comment')) {
            if ($order->buyer_id === Auth::id()) {
                $order->buyer_comment = $request->comment;
            } else {
                $order->seller_comment = $request->comment;
            }
        }
        
        $order->save();
        
        // Загружаем связи для уведомления
        $order->load(['buyer', 'seller', 'listable']);
        
        // Уведомляем другую сторону об отмене сделки
        $notifiedUser = Auth::id() === $order->buyer_id ? $order->seller : $order->buyer;
        $notifiedUser->notify(new OrderStatusChanged($order, 'cancelled', $cancelledBy));
        
        return response()->json(['success' => true, 'order' => $order]);
    }

    /**
     * Получить информацию о возможности оставить отзыв
     */
    public function canReview(Order $order)
    {
        $canReview = $order->status === Order::STATUS_COMPLETED;
        $hasReviewed = false;
        
        if ($canReview) {
            $hasReviewed = Review::where('seller_id', $order->seller_id)
                ->where('buyer_id', Auth::id())
                ->where('listable_type', $order->listable_type)
                ->where('listable_id', $order->listable_id)
                ->exists();
        }
        
        return response()->json([
            'can_review' => $canReview && !$hasReviewed,
            'has_reviewed' => $hasReviewed,
            'order_id' => $order->id,
            'seller_id' => $order->seller_id,
        ]);
    }
}