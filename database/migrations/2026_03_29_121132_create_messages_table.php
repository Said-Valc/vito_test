<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // Отправитель и получатель
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            
            // Полиморфные поля для привязки к любому типу объявлений
            $table->nullableMorphs('listable'); // создает listable_id и listable_type
            
            // Тема чата (обычно название объявления)
            $table->string('subject')->nullable();
            
            // Текст сообщения
            $table->text('text');
            
            // Время прочтения
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Индексы для оптимизации запросов
            $table->index(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'read_at']);
            
            // Индекс для группировки чатов по объявлениям
            $table->index(['sender_id', 'receiver_id', 'listable_type', 'listable_id'], 'idx_conversation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};