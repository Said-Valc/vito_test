<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rabota_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('rabota_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('about_me');
            $table->string('full_name');
            $table->integer('age');
            $table->string('gender');
            $table->string('education');
            $table->string('specialization');
            $table->json('work_experience')->nullable();
            $table->json('skills')->nullable();
            $table->json('languages')->nullable();
            $table->decimal('salary_expectation', 10, 2)->nullable();
            $table->string('employment_type')->nullable();
            $table->string('work_schedule')->nullable();
            $table->string('city');
            $table->string('phone');
            $table->string('email');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
            $table->index('specialization');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rabota_resumes');
    }
};