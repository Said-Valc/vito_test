<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait Filterable
{
    /**
     * Scope для фильтрации объявлений
     */
    public function scopeFilter($query, Request $request)
    {
        // Поиск по тексту
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Фильтр по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Фильтр по тегу (если есть поле tags)
        if ($request->filled('tag')) {
            $query->where('tags', 'like', '%' . $request->tag . '%');
        }

        // Фильтр по цене
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Фильтр по городу
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        return $query;
    }
}