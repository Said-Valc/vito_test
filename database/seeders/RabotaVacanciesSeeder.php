<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RabotaVacancy;
use App\Models\User;

class RabotaVacanciesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $vacancies = [
            [
                'user_id' => $user->id,
                'category_id' => 1, // Вакансии
                'title' => 'Senior PHP Developer (Laravel)',
                'description' => 'Ищем опытного PHP разработчика со знанием Laravel. Работа над крупным проектом, дружный коллектив, гибкий график.',
                'company_name' => 'ООО "Технологии Будущего"',
                'company_description' => 'IT-компания, специализирующаяся на разработке высоконагруженных проектов',
                'salary_from' => 250000,
                'salary_to' => 350000,
                'salary_type' => 'В месяц',
                'employment_type' => 'Полная',
                'work_schedule' => 'Полный день',
                'experience' => '3-6 лет',
                'education' => 'Высшее',
                'requirements' => json_encode([
                    'PHP 8.x',
                    'Laravel 10.x',
                    'MySQL',
                    'Redis',
                    'Docker',
                    'Git'
                ]),
                'conditions' => json_encode([
                    'Оформление по ТК РФ',
                    'ДМС со стоматологией',
                    'Гибкий график',
                    'Можно удаленно'
                ]),
                'city' => 'Москва',
                'address' => 'ул. Тверская, д. 15, офис 301',
                'phone' => '+7 (999) 123-45-67',
                'email' => 'hr@future-tech.ru',
                'expires_at' => now()->addDays(30),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($vacancies as $vacancy) {
            RabotaVacancy::create($vacancy);
        }
    }
}