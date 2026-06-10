<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nedvizhimost_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('nedvizhimost_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->string('action');
            $table->integer('rooms')->nullable();
            $table->decimal('area_total', 8, 2)->nullable();
            $table->decimal('area_living', 8, 2)->nullable();
            $table->decimal('area_kitchen', 8, 2)->nullable();
            $table->integer('floor')->nullable();
            $table->integer('floors_total')->nullable();
            $table->string('building_type')->nullable();
            $table->integer('year_built')->nullable();
            $table->string('condition')->nullable();
            $table->boolean('has_balcony')->default(false);
            $table->boolean('has_parking')->default(false);
            $table->boolean('has_furniture')->default(false);
            $table->string('address');
            $table->string('city');
            $table->string('district')->nullable();
            $table->json('photos')->nullable();
            $table->string('phone');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'action', 'is_active']);
            $table->index('price');
            $table->index('city');
            $table->index('rooms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nedvizhimost_listings');
    }
};