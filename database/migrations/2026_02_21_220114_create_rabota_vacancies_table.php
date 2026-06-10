<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rabota_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('rabota_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('company_name');
            $table->text('company_description')->nullable();
            $table->decimal('salary_from', 10, 2)->nullable();
            $table->decimal('salary_to', 10, 2)->nullable();
            $table->string('salary_type')->nullable();
            $table->string('employment_type');
            $table->string('work_schedule');
            $table->string('experience')->nullable();
            $table->string('education')->nullable();
            $table->json('requirements')->nullable();
            $table->json('conditions')->nullable();
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
            $table->index('company_name');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rabota_vacancies');
    }
};