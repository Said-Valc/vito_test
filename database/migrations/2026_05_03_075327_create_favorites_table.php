<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('listable_type'); // Модель объявления (AutoListing, NedvizhimostListing, ...)
            $table->unsignedBigInteger('listable_id');
            $table->timestamps();

            // Уникальность: один пользователь не может добавить одно объявление дважды
            $table->unique(['user_id', 'listable_type', 'listable_id']);
            
            // Индекс для быстрого поиска
            $table->index(['listable_type', 'listable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('favorites');
    }
};