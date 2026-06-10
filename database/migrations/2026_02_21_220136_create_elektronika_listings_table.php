<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elektronika_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('elektronika_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('brand');
            $table->string('model');
            $table->string('condition');
            $table->json('specifications')->nullable();
            $table->string('warranty')->nullable();
            $table->boolean('is_original')->default(true);
            $table->json('photos')->nullable();
            $table->string('city');
            $table->string('phone');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
            $table->index('price');
            $table->index('brand');
            $table->index('condition');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elektronika_listings');
    }
};