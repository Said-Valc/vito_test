<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'listable_type',
        'listable_id',
        'subject',
        'text',
        'read_at'
    ];
    
    protected $casts = [
        'read_at' => 'datetime',
    ];
    
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    
    public function listable()
    {
        return $this->morphTo();
    }
    
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }
    
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    
    /**
     * Получить тип объявления из модели
     */
    private static function getListingTypeFromModel($model)
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
     * Получить диалоги пользователя, сгруппированные по объявлениям
     */
    public static function getConversations($userId)
    {
        // Получаем все сообщения пользователя
        $messages = self::where(function($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        })->orderBy('created_at', 'desc')->get();
        
        // Группируем по уникальным парам (пользователь + объявление)
        $conversations = [];
        
        foreach ($messages as $message) {
            $otherUserId = $message->sender_id === $userId 
                ? $message->receiver_id 
                : $message->sender_id;
            
            $minUser = min($userId, $otherUserId);
            $maxUser = max($userId, $otherUserId);

            // ВАЖНО: Правильно формируем ключ для группировки
            $listablePart = $message->listable_type && $message->listable_id
                ? "{$message->listable_type}_{$message->listable_id}"
                : "general";

            $listingKey = "{$minUser}_{$maxUser}_{$listablePart}";
            
            if (!isset($conversations[$listingKey])) {
                $conversations[$listingKey] = [
                    'user' => User::find($otherUserId),
                    'listable' => $message->listable,
                    'subject' => $message->subject 
                        ?? ($message->listable ? $message->listable->title : 'Общий чат'),
                    'last_message' => $message,
                    'messages' => [],
                    'unread_count' => 0,
                ];
                
                // Добавляем listing_type для listable
                if ($conversations[$listingKey]['listable']) {
                    $conversations[$listingKey]['listable']->listing_type = 
                        self::getListingTypeFromModel($message->listable);
                }
            }
            
            if ($message->receiver_id === $userId && is_null($message->read_at)) {
                $conversations[$listingKey]['unread_count']++;
            }
            $conversations[$listingKey]['messages'][] = $message;
        }
        
        return array_values($conversations);
    }
}