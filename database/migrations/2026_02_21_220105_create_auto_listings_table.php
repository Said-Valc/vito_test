<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('auto_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->integer('mileage')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('engine_capacity')->nullable();
            $table->string('transmission')->nullable();
            $table->string('drive_type')->nullable();
            $table->string('color')->nullable();
            $table->string('body_type')->nullable();
            $table->string('condition')->nullable();
            $table->boolean('is_accident')->default(false);
            $table->boolean('is_credit')->default(false);
            $table->json('photos')->nullable();
            $table->string('city');
            $table->string('phone');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
            $table->index('price');
            $table->index('year');
            $table->index('brand');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_listings');
    }
};