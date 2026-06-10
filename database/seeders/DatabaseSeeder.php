<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(2)->create();
        Listing::factory(20)->create();
        
        // Сначала создаем локации
        $this->call([
            LocationSeeder::class,
        ]);
        
        // Затем категории
        $this->call([
            AutoCategorySeeder::class,
            NedvizhimostCategorySeeder::class,
            RabotaCategorySeeder::class,
            UslugiCategorySeeder::class,
            LichnieVeschiCategorySeeder::class,
            DlyaDomaCategorySeeder::class,
            ElektronikaCategorySeeder::class,
            HobbyCategorySeeder::class,
        ]);

        // Затем объявления (они используют локации)
        $this->call([
            AutoListingsSeeder::class,
            NedvizhimostListingsSeeder::class,
            RabotaVacanciesSeeder::class,
            RabotaResumesSeeder::class,
            UslugiListingsSeeder::class,
            LichnieVeschiListingsSeeder::class,
            DlyaDomaListingsSeeder::class,
            ElektronikaListingsSeeder::class,
            HobbyListingsSeeder::class,
        ]);
    }
}