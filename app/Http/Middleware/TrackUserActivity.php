<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $expiresAt = now()->addMinutes(5);
            Cache::put('user-is-online-' . Auth::id(), true, $expiresAt);
            
            // Обновляем last_seen в базе данных
            Auth::user()->update(['updated_at' => now()]);
        }
        
        return $next($request);
    }
}