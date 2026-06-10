<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RabotaResume;
use App\Models\User;

class RabotaResumesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $resumes = [
            [
                'user_id' => $user->id,
                'category_id' => 2, // Резюме
                'title' => 'Senior Full Stack Developer ищет работу',
                'about_me' => 'Опытный разработчик с 8-летним стажем. Ищу интересный проект с современным стеком технологий. Ответственный, коммуникабельный, умею работать в команде.',
                'full_name' => 'Иванов Иван Иванович',
                'age' => 32,
                'gender' => 'Мужской',
                'education' => 'Высшее (МГУ, факультет ВМК)',
                'specialization' => 'Веб-разработка',
                'work_experience' => json_encode([
                    ['company' => 'Яндекс', 'position' => 'Senior Developer', 'years' => '2020-2024'],
                    ['company' => 'Google', 'position' => 'Middle Developer', 'years' => '2017-2020'],
                ]),
                'skills' => json_encode([
                    'PHP/Laravel',
                    'Vue.js/Nuxt',
                    'Python',
                    'MySQL/PostgreSQL',
                    'Docker/Kubernetes',
                    'AWS'
                ]),
                'languages' => json_encode([
                    'Русский (родной)',
                    'Английский (Upper-Intermediate)'
                ]),
                'salary_expectation' => 300000,
                'employment_type' => 'Полная',
                'work_schedule' => 'Полный день',
                'city' => 'Москва',
                'phone' => '+7 (999) 234-56-78',
                'email' => 'ivan.ivanov@email.ru',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($resumes as $resume) {
            RabotaResume::create($resume);
        }
    }
}