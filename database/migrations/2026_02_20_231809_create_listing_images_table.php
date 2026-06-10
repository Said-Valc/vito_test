<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Проверяем, существует ли уже таблица
        if (!Schema::hasTable('listing_images')) {
            Schema::create('listing_images', function (Blueprint $table) {
                $table->id();
                $table->morphs('listable'); // Создает listable_type и listable_id + индекс
                $table->string('path');
                $table->integer('position')->default(0);
                $table->boolean('is_main')->default(false);
                $table->timestamps();
                
                // Дополнительные индексы
                $table->index('position');
                // Индекс для listable уже создается методом morphs()
            });
        } else {
            // Если таблица существует, проверяем и добавляем недостающие столбцы
            Schema::table('listing_images', function (Blueprint $table) {
                if (!Schema::hasColumn('listing_images', 'listable_type')) {
                    $table->string('listable_type')->after('id');
                }
                if (!Schema::hasColumn('listing_images', 'listable_id')) {
                    $table->unsignedBigInteger('listable_id')->after('listable_type');
                }
                if (!Schema::hasColumn('listing_images', 'path')) {
                    $table->string('path')->after('listable_id');
                }
                if (!Schema::hasColumn('listing_images', 'position')) {
                    $table->integer('position')->default(0)->after('path');
                }
                if (!Schema::hasColumn('listing_images', 'is_main')) {
                    $table->boolean('is_main')->default(false)->after('position');
                }
                
                // Проверяем и создаем индексы, если их нет
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('listing_images');
                
                if (!array_key_exists('listing_images_listable_type_listable_id_index', $indexes)) {
                    $table->index(['listable_type', 'listable_id']);
                }
                
                if (!array_key_exists('listing_images_position_index', $indexes)) {
                    $table->index('position');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_images');
    }
};