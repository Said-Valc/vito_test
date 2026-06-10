<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hobby_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('hobby_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('brand')->nullable();
            $table->string('type')->nullable();
            $table->string('condition');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->json('specifications')->nullable();
            $table->json('photos')->nullable();
            $table->string('city');
            $table->string('phone');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
            $table->index('price');
            $table->index('condition');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hobby_listings');
    }
};