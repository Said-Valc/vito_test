<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Список всех таблиц объявлений
        $tables = [
            'auto_listings',
            'nedvizhimost_listings',
            'elektronika_listings',
            'hobby_listings',
            'rabota_vacancies',
            'rabota_resumes',
            'uslugi_listings',
            'lichnie_veschi_listings',
            'dlya_doma_listings',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Добавляем колонки только если их нет
                    if (!Schema::hasColumn($tableName, 'latitude')) {
                        $table->decimal('latitude', 10, 7)->nullable()->after('city');
                    }
                    
                    if (!Schema::hasColumn($tableName, 'longitude')) {
                        $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                    }
                    
                    if (!Schema::hasColumn($tableName, 'location_id')) {
                        $table->unsignedBigInteger('location_id')->nullable()->after('longitude');
                        
                        // Добавляем внешний ключ
                        $table->foreign('location_id')
                              ->references('id')
                              ->on('locations')
                              ->onDelete('set null');
                    }
                });
            }
        }
    }

    public function down()
    {
        $tables = [
            'auto_listings',
            'nedvizhimost_listings',
            'elektronika_listings',
            'hobby_listings',
            'rabota_vacancies',
            'rabota_resumes',
            'uslugi_listings',
            'lichnie_veschi_listings',
            'dlya_doma_listings',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Удаляем foreign key если существует
                    $foreignKeys = collect(Schema::getForeignKeys($tableName));
                    $hasForeignKey = $foreignKeys->contains(function ($fk) {
                        return in_array('location_id', $fk['columns']);
                    });
                    
                    if ($hasForeignKey) {
                        $table->dropForeign(['location_id']);
                    }
                    
                    // Удаляем колонки если существуют
                    $columnsToDrop = [];
                    if (Schema::hasColumn($tableName, 'location_id')) {
                        $columnsToDrop[] = 'location_id';
                    }
                    if (Schema::hasColumn($tableName, 'latitude')) {
                        $columnsToDrop[] = 'latitude';
                    }
                    if (Schema::hasColumn($tableName, 'longitude')) {
                        $columnsToDrop[] = 'longitude';
                    }
                    
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }
        }
    }
};