<?php
// database/migrations/2026_04_09_000000_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Покупатель и продавец
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            
            // Связь с объявлением (полиморфная)
            $table->morphs('listable');
            
            // Информация о сделке
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->default('pending'); // pending, active, completed, cancelled, disputed
            
            // Даты
            $table->timestamp('buyer_confirmed_at')->nullable();   // покупатель подтвердил получение
            $table->timestamp('seller_confirmed_at')->nullable();  // продавец подтвердил отправку
            $table->timestamp('completed_at')->nullable();         // сделка завершена
            $table->timestamp('cancelled_at')->nullable();         // сделка отменена
            
            // Комментарии
            $table->text('buyer_comment')->nullable();
            $table->text('seller_comment')->nullable();
            
            $table->timestamps();
            
            // Индексы
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};