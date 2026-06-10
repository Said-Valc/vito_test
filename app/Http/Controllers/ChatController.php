<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    /**
     * Получение списка диалогов (с группировкой по объявлениям)
     */
    public function conversations()
    {
        $userId = Auth::id();
        
        // Получаем все сообщения пользователя с группировкой
        $conversations = Message::getConversations($userId);
        
        // Добавляем счетчики непрочитанных и listing_type
        foreach ($conversations as &$conv) {
            $conv['unread_count'] = Message::where('sender_id', $conv['user']->id)
                ->where('receiver_id', $userId)
                ->whereNull('read_at')
                ->when($conv['listable'], function($q) use ($conv) {
                    return $q->where('listable_type', get_class($conv['listable']))
                             ->where('listable_id', $conv['listable']->id);
                }, function($q) {
                    return $q->whereNull('listable_type');
                })
                ->count();
            
            // Добавляем listing_type для listable
            if ($conv['listable']) {
                $conv['listable']->listing_type = $this->getListingTypeFromModel($conv['listable']);
            }
        }
        
        // Сортируем по дате последнего сообщения
        usort($conversations, fn($a, $b) => 
            $b['last_message']->created_at <=> $a['last_message']->created_at
        );
        
        return response()->json($conversations);
    }
    
    /**
     * Получение типа объявления из модели
     */
    private function getListingTypeFromModel($model)
    {
        if (!$model) return null;
        
        $class = get_class($model);
        
        $mapping = [
            'App\\Models\\AutoListing' => 'auto',
            'App\\Models\\NedvizhimostListing' => 'nedvizhimost',
            'App\\Models\\ElektronikaListing' => 'elektronika',
            'App\\Models\\HobbyListing' => 'hobby',
            'App\\Models\\RabotaVacancy' => 'vacancy',
            'App\\Models\\RabotaResume' => 'resume',
            'App\\Models\\UslugiListing' => 'uslugi',
            'App\\Models\\LichnieVeschiListing' => 'lichnie_veschi',
            'App\\Models\\DlyaDomaListing' => 'dlya_doma',
        ];
        
        return $mapping[$class] ?? null;
    }
    
    /**
     * Получение сообщений диалога (конкретное объявление)
     */
    public function getMessages(Request $request, User $recipient)
    {
        $userId = Auth::id();

        $listingType = $request->get('listing_type');
        $listingId = $request->get('listing_id');

        \Log::info('📥 getMessages called', [
            'user' => $userId,
            'recipient' => $recipient->id,
            'listing_type' => $listingType,
            'listing_id' => $listingId
        ]);

        $query = Message::where(function ($q) use ($userId, $recipient) {
            $q->where(function ($q2) use ($userId, $recipient) {
                $q2->where('sender_id', $userId)
                ->where('receiver_id', $recipient->id);
            })->orWhere(function ($q2) use ($userId, $recipient) {
                $q2->where('sender_id', $recipient->id)
                ->where('receiver_id', $userId);
            });
        });

        // КРИТИЧЕСКИЙ ФИКС: Проверяем наличие параметров
        if ($listingId && $listingType) {
            $listableType = $this->formatModelName($listingType);
            $query->where('listable_type', 'App\\Models\\' . $listableType)
                ->where('listable_id', $listingId);
        } else {
            // если чат БЕЗ объявления
            $query->whereNull('listable_id');
        }

        $messages = $query->with(['sender', 'receiver', 'listable'])
            ->orderBy('created_at', 'asc')
            ->get();

        \Log::info('✅ Найдено сообщений: ' . $messages->count());

        // Добавляем listing_type для listable в каждом сообщении
        foreach ($messages as $message) {
            if ($message->listable) {
                $message->listable->listing_type = $listingType ?? $this->getListingTypeFromModel($message->listable);
            }
        }

        return $messages;
    }
    
    /**
     * Отправка сообщения
     */
    public function store(Request $request, User $recipient)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
            'listing_type' => 'nullable|string',
            'listing_id' => 'nullable|integer',
            'subject' => 'nullable|string|max:255',
        ]);
        
        \Log::info('📤 Отправка сообщения', [
            'sender' => Auth::id(),
            'recipient' => $recipient->id,
            'listing_type' => $request->listing_type,
            'listing_id' => $request->listing_id
        ]);
        
        $data = [
            'sender_id' => Auth::id(),
            'receiver_id' => $recipient->id,
            'text' => $request->text,
            'read_at' => null,
            'subject' => $request->subject,
        ];
        
        // Привязываем к объявлению если указано
        if ($request->listing_type && $request->listing_id) {
            $listableType = $this->formatModelName($request->listing_type);
            $data['listable_type'] = 'App\\Models\\' . $listableType;
            $data['listable_id'] = $request->listing_id;
            
            // Если тема не указана, пытаемся получить из объявления
            if (empty($data['subject'])) {
                $listing = $this->findListing($request->listing_type, $request->listing_id);
                if ($listing) {
                    $data['subject'] = $listing->title;
                }
            }
        }
        
        $message = Message::create($data);
        $message->load(['sender', 'receiver', 'listable']);
        
        // Добавляем listing_type для фронтенда
        if ($message->listable) {
            $message->listable->listing_type = $request->listing_type;
        }
        
        broadcast(new MessageSent($message))->toOthers();
        
        return response()->json($message);
    }
    
    /**
     * Пометка сообщений как прочитанных
     */
    public function markAsRead(Request $request, User $sender)
    {
        \Log::info('✓ Отмечаем как прочитанные', [
            'sender' => $sender->id,
            'receiver' => Auth::id(),
            'listing_type' => $request->listing_type,
            'listing_id' => $request->listing_id
        ]);
        
        $query = Message::where('sender_id', $sender->id)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at');
        
        // Помечаем только сообщения конкретного диалога
        if ($request->listing_type && $request->listing_id) {
            $listableType = $this->formatModelName($request->listing_type);
            $query->where('listable_type', 'App\\Models\\' . $listableType)
                  ->where('listable_id', $request->listing_id);
        } else {
            // Если нет объявления - помечаем только общие сообщения
            $query->whereNull('listable_id');
        }
        
        $count = $query->update(['read_at' => now()]);
        
        \Log::info('✓ Отмечено сообщений: ' . $count);
        
        return response()->json(['success' => true, 'count' => $count]);
    }
    
    /**
     * Страница чата
     */
    public function index()
    {
        return Inertia::render('Chat/Index', [
            'conversations' => Message::getConversations(Auth::id()),
            'authUser' => Auth::user(),
        ]);
    }
    
    /**
     * Показ чата с пользователем по конкретному объявлению
     */
    public function show(Request $request, User $user)
    {
        $authId = Auth::id();
        $listingType = $request->get('listing_type');
        $listingId = $request->get('listing_id');
        $listing = null;
        
        if ($listingType && $listingId) {
            $listing = $this->findListing($listingType, $listingId);
            if ($listing) {
                $listing->listing_type = $listingType;
            }
        }
        
        // Помечаем сообщения как прочитанные
        $query = Message::where('sender_id', $user->id)
            ->where('receiver_id', $authId)
            ->whereNull('read_at');
        
        if ($listingType && $listingId) {
            $listableType = $this->formatModelName($listingType);
            $query->where('listable_type', 'App\\Models\\' . $listableType)
                  ->where('listable_id', $listingId);
        } else {
            $query->whereNull('listable_id');
        }
        
        $query->update(['read_at' => now()]);
        
        // Получаем сообщения
        $messages = Message::where(function ($q) use ($authId, $user) {
            $q->where(function ($q2) use ($authId, $user) {
                $q2->where('sender_id', $authId)
                ->where('receiver_id', $user->id);
            })->orWhere(function ($q2) use ($authId, $user) {
                $q2->where('sender_id', $user->id)
                ->where('receiver_id', $authId);
            });
        });
        
        if ($listingType && $listingId) {
            $listableType = $this->formatModelName($listingType);
            $messages->where('listable_type', 'App\\Models\\' . $listableType)
                     ->where('listable_id', $listingId);
        } else {
            $messages->whereNull('listable_id');
        }
        
        $messages = $messages->with(['sender', 'receiver', 'listable'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Добавляем listing_type для listable
        foreach ($messages as $message) {
            if ($message->listable) {
                $message->listable->listing_type = $listingType ?? $this->getListingTypeFromModel($message->listable);
            }
        }
        
        return Inertia::render('Chat/Show', [
            'recipient' => $user,
            'messages' => $messages,
            'listing' => $listing,
            'authUser' => Auth::user(),
        ]);
    }

    public function onlineStatus(User $user)
    {
        try {
            // Проверяем, был ли пользователь активен в последние 5 минут
            $isOnline = Cache::has('user-is-online-' . $user->id);
            
            return response()->json([
                'online' => $isOnline,
                'last_seen' => $isOnline ? null : $user->updated_at
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking online status: ' . $e->getMessage());
            
            return response()->json([
                'online' => false,
                'error' => 'Unable to check online status'
            ], 200);
        }
    }

    /**
     * Обновление онлайн статуса (вызывается периодически с фронта)
     */
    public function updateOnlineStatus(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user) {
                // Сохраняем статус онлайн на 5 минут
                Cache::put('user-is-online-' . $user->id, true, now()->addMinutes(5));
                
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false], 401);
        } catch (\Exception $e) {
            \Log::error('Error updating online status: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Вспомогательная функция форматирования имени модели
     */
    private function formatModelName($type)
    {
        $mapping = [
            'auto' => 'AutoListing',
            'nedvizhimost' => 'NedvizhimostListing',
            'elektronika' => 'ElektronikaListing',
            'hobby' => 'HobbyListing',
            'vacancy' => 'RabotaVacancy',
            'resume' => 'RabotaResume',
            'uslugi' => 'UslugiListing',
            'lichnie_veschi' => 'LichnieVeschiListing',
            'dlya_doma' => 'DlyaDomaListing',
        ];
        
        return $mapping[$type] ?? ucfirst($type);
    }
    
    /**
     * Найти объявление по типу и ID
     */
    private function findListing($type, $id)
    {
        $modelClass = 'App\\Models\\' . $this->formatModelName($type);
        
        if (class_exists($modelClass)) {
            return $modelClass::find($id);
        }
        
        return null;
    }
}