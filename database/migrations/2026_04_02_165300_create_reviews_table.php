<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // Продавец (кому оставляют отзыв)
            $table->foreignId('seller_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Покупатель (кто оставляет отзыв)
            $table->foreignId('buyer_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Привязка к конкретному объявлению (полиморфная)
            $table->nullableMorphs('listable');
            
            // Оценка от 1 до 5
            $table->unsignedTinyInteger('rating');
            
            // Текст отзыва
            $table->text('comment')->nullable();
            
            // Статусы
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_edited')->default(false);
            
            // Ответ продавца
            $table->text('seller_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            $table->timestamps();
            
            // Уникальность: один покупатель - один отзыв на одну конкретную сделку
            $table->unique(['buyer_id', 'listable_type', 'listable_id'], 'unique_review_per_deal');
            
            // Индексы для быстрых запросов
            $table->index(['seller_id', 'rating']);
            $table->index(['seller_id', 'created_at']);
            $table->index(['seller_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};