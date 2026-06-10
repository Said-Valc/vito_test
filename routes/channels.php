<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Message;

Broadcast::channel('presence.chat.{id}', function ($user, $id) {
    return ['id' => $user->id, 'name' => $user->name];
});

// ЕДИНЫЙ КАНАЛ ЧАТА
Broadcast::channel('chat.{id}', function ($user, $id) {
    return true;
});