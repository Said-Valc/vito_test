<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uslugi_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('uslugi_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('service_type');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_type')->nullable();
            $table->json('portfolio')->nullable();
            $table->integer('experience_years')->nullable();
            $table->json('certificates')->nullable();
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->json('work_schedule')->nullable();
            $table->boolean('has_guarantee')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'service_type', 'is_active']);
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uslugi_listings');
    }
};