<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run()
    {
        // Российские города
        $russianCities = [
            ['name' => 'Москва', 'region' => 'Московская область', 'country' => 'Россия', 'latitude' => 55.7558, 'longitude' => 37.6173, 'population' => 12655000],
            ['name' => 'Санкт-Петербург', 'region' => 'Ленинградская область', 'country' => 'Россия', 'latitude' => 59.9343, 'longitude' => 30.3351, 'population' => 5384000],
            ['name' => 'Новосибирск', 'region' => 'Новосибирская область', 'country' => 'Россия', 'latitude' => 55.0084, 'longitude' => 82.9357, 'population' => 1626000],
            ['name' => 'Екатеринбург', 'region' => 'Свердловская область', 'country' => 'Россия', 'latitude' => 56.8389, 'longitude' => 60.6057, 'population' => 1495000],
            ['name' => 'Казань', 'region' => 'Республика Татарстан', 'country' => 'Россия', 'latitude' => 55.7879, 'longitude' => 49.1233, 'population' => 1257000],
            ['name' => 'Нижний Новгород', 'region' => 'Нижегородская область', 'country' => 'Россия', 'latitude' => 56.2965, 'longitude' => 43.9361, 'population' => 1244000],
            ['name' => 'Челябинск', 'region' => 'Челябинская область', 'country' => 'Россия', 'latitude' => 55.1644, 'longitude' => 61.4368, 'population' => 1187000],
            ['name' => 'Самара', 'region' => 'Самарская область', 'country' => 'Россия', 'latitude' => 53.1959, 'longitude' => 50.1002, 'population' => 1144000],
            ['name' => 'Омск', 'region' => 'Омская область', 'country' => 'Россия', 'latitude' => 54.9893, 'longitude' => 73.3682, 'population' => 1139000],
            ['name' => 'Ростов-на-Дону', 'region' => 'Ростовская область', 'country' => 'Россия', 'latitude' => 47.2357, 'longitude' => 39.7015, 'population' => 1138000],
            ['name' => 'Уфа', 'region' => 'Республика Башкортостан', 'country' => 'Россия', 'latitude' => 54.7348, 'longitude' => 55.9578, 'population' => 1125000],
            ['name' => 'Красноярск', 'region' => 'Красноярский край', 'country' => 'Россия', 'latitude' => 56.0106, 'longitude' => 92.8525, 'population' => 1094000],
            ['name' => 'Воронеж', 'region' => 'Воронежская область', 'country' => 'Россия', 'latitude' => 51.6720, 'longitude' => 39.1843, 'population' => 1050000],
            ['name' => 'Пермь', 'region' => 'Пермский край', 'country' => 'Россия', 'latitude' => 58.0105, 'longitude' => 56.2502, 'population' => 1049000],
            ['name' => 'Волгоград', 'region' => 'Волгоградская область', 'country' => 'Россия', 'latitude' => 48.7080, 'longitude' => 44.5133, 'population' => 1005000],
            ['name' => 'Краснодар', 'region' => 'Краснодарский край', 'country' => 'Россия', 'latitude' => 45.0355, 'longitude' => 38.9753, 'population' => 948000],
            ['name' => 'Саратов', 'region' => 'Саратовская область', 'country' => 'Россия', 'latitude' => 51.5336, 'longitude' => 46.0343, 'population' => 830000],
            ['name' => 'Тюмень', 'region' => 'Тюменская область', 'country' => 'Россия', 'latitude' => 57.1530, 'longitude' => 65.5343, 'population' => 816000],
            ['name' => 'Тольятти', 'region' => 'Самарская область', 'country' => 'Россия', 'latitude' => 53.5303, 'longitude' => 49.3461, 'population' => 693000],
            ['name' => 'Ижевск', 'region' => 'Удмуртская Республика', 'country' => 'Россия', 'latitude' => 56.8498, 'longitude' => 53.2045, 'population' => 648000],
            ['name' => 'Барнаул', 'region' => 'Алтайский край', 'country' => 'Россия', 'latitude' => 53.3548, 'longitude' => 83.7698, 'population' => 631000],
            ['name' => 'Ульяновск', 'region' => 'Ульяновская область', 'country' => 'Россия', 'latitude' => 54.3142, 'longitude' => 48.4031, 'population' => 627000],
            ['name' => 'Иркутск', 'region' => 'Иркутская область', 'country' => 'Россия', 'latitude' => 52.2869, 'longitude' => 104.3050, 'population' => 623000],
            ['name' => 'Хабаровск', 'region' => 'Хабаровский край', 'country' => 'Россия', 'latitude' => 48.4802, 'longitude' => 135.0718, 'population' => 616000],
            ['name' => 'Ярославль', 'region' => 'Ярославская область', 'country' => 'Россия', 'latitude' => 57.6261, 'longitude' => 39.8845, 'population' => 608000],
            ['name' => 'Владивосток', 'region' => 'Приморский край', 'country' => 'Россия', 'latitude' => 43.1332, 'longitude' => 131.9113, 'population' => 606000],
            ['name' => 'Махачкала', 'region' => 'Республика Дагестан', 'country' => 'Россия', 'latitude' => 42.9849, 'longitude' => 47.5047, 'population' => 604000],
            ['name' => 'Томск', 'region' => 'Томская область', 'country' => 'Россия', 'latitude' => 56.4846, 'longitude' => 84.9476, 'population' => 576000],
            ['name' => 'Оренбург', 'region' => 'Оренбургская область', 'country' => 'Россия', 'latitude' => 51.7682, 'longitude' => 55.0970, 'population' => 572000],
            ['name' => 'Кемерово', 'region' => 'Кемеровская область', 'country' => 'Россия', 'latitude' => 55.3549, 'longitude' => 86.0873, 'population' => 556000],
        ];

        // Турецкие города
        $turkishCities = [
            ['name' => 'Стамбул', 'region' => 'Мраморноморский регион', 'country' => 'Турция', 'latitude' => 41.0082, 'longitude' => 28.9784, 'population' => 15462000],
            ['name' => 'Анкара', 'region' => 'Центральная Анатолия', 'country' => 'Турция', 'latitude' => 39.9334, 'longitude' => 32.8597, 'population' => 5663000],
            ['name' => 'Измир', 'region' => 'Эгейский регион', 'country' => 'Турция', 'latitude' => 38.4237, 'longitude' => 27.1428, 'population' => 4367000],
            ['name' => 'Бурса', 'region' => 'Мраморноморский регион', 'country' => 'Турция', 'latitude' => 40.1828, 'longitude' => 29.0664, 'population' => 3056000],
            ['name' => 'Анталья', 'region' => 'Средиземноморский регион', 'country' => 'Турция', 'latitude' => 36.8969, 'longitude' => 30.7133, 'population' => 2511000],
            ['name' => 'Адана', 'region' => 'Средиземноморский регион', 'country' => 'Турция', 'latitude' => 37.0000, 'longitude' => 35.3213, 'population' => 2258000],
            ['name' => 'Конья', 'region' => 'Центральная Анатолия', 'country' => 'Турция', 'latitude' => 37.8715, 'longitude' => 32.4846, 'population' => 2232000],
            ['name' => 'Газиантеп', 'region' => 'Юго-Восточная Анатолия', 'country' => 'Турция', 'latitude' => 37.0662, 'longitude' => 37.3833, 'population' => 2101000],
            ['name' => 'Шанлыурфа', 'region' => 'Юго-Восточная Анатолия', 'country' => 'Турция', 'latitude' => 37.1592, 'longitude' => 38.7969, 'population' => 2073000],
            ['name' => 'Мерсин', 'region' => 'Средиземноморский регион', 'country' => 'Турция', 'latitude' => 36.8000, 'longitude' => 34.6167, 'population' => 1868000],
            ['name' => 'Кайсери', 'region' => 'Центральная Анатолия', 'country' => 'Турция', 'latitude' => 38.7205, 'longitude' => 35.4826, 'population' => 1421000],
            ['name' => 'Эскишехир', 'region' => 'Центральная Анатолия', 'country' => 'Турция', 'latitude' => 39.7767, 'longitude' => 30.5206, 'population' => 887000],
            ['name' => 'Денизли', 'region' => 'Эгейский регион', 'country' => 'Турция', 'latitude' => 37.7765, 'longitude' => 29.0864, 'population' => 1039000],
            ['name' => 'Самсун', 'region' => 'Черноморский регион', 'country' => 'Турция', 'latitude' => 41.2867, 'longitude' => 36.3300, 'population' => 1335000],
            ['name' => 'Аланья', 'region' => 'Средиземноморский регион', 'country' => 'Турция', 'latitude' => 36.5431, 'longitude' => 31.9991, 'population' => 312000],
            ['name' => 'Фетхие', 'region' => 'Эгейский регион', 'country' => 'Турция', 'latitude' => 36.6217, 'longitude' => 29.1164, 'population' => 157000],
            ['name' => 'Бодрум', 'region' => 'Эгейский регион', 'country' => 'Турция', 'latitude' => 37.0344, 'longitude' => 27.4305, 'population' => 175000],
            ['name' => 'Мармарис', 'region' => 'Эгейский регион', 'country' => 'Турция', 'latitude' => 36.8550, 'longitude' => 28.2747, 'population' => 94000],
            ['name' => 'Кушадасы', 'region' => 'Эгейский регион', 'country' => 'Турция', 'latitude' => 37.8600, 'longitude' => 27.2600, 'population' => 113000],
            ['name' => 'Трабзон', 'region' => 'Черноморский регион', 'country' => 'Турция', 'latitude' => 41.0053, 'longitude' => 39.7225, 'population' => 807000],
            ['name' => 'Диярбакыр', 'region' => 'Юго-Восточная Анатолия', 'country' => 'Турция', 'latitude' => 37.9246, 'longitude' => 40.2110, 'population' => 1756000],
            ['name' => 'Эрзурум', 'region' => 'Восточная Анатолия', 'country' => 'Турция', 'latitude' => 39.9000, 'longitude' => 41.2700, 'population' => 762000],
            ['name' => 'Ван', 'region' => 'Восточная Анатолия', 'country' => 'Турция', 'latitude' => 38.4892, 'longitude' => 43.4087, 'population' => 525000],
            ['name' => 'Малатья', 'region' => 'Восточная Анатолия', 'country' => 'Турция', 'latitude' => 38.3552, 'longitude' => 38.3095, 'population' => 797000],
            ['name' => 'Чанаккале', 'region' => 'Мраморноморский регион', 'country' => 'Турция', 'latitude' => 40.1553, 'longitude' => 26.4142, 'population' => 540000],
            ['name' => 'Измит', 'region' => 'Мраморноморский регион', 'country' => 'Турция', 'latitude' => 40.7650, 'longitude' => 29.9400, 'population' => 363000],
            ['name' => 'Маниса', 'region' => 'Эгейский регион', 'country' => 'Турция', 'latitude' => 38.6131, 'longitude' => 27.4289, 'population' => 1420000],
            ['name' => 'Кемер', 'region' => 'Средиземноморский регион', 'country' => 'Турция', 'latitude' => 36.6000, 'longitude' => 30.5667, 'population' => 45000],
            ['name' => 'Белек', 'region' => 'Средиземноморский регион', 'country' => 'Турция', 'latitude' => 36.8625, 'longitude' => 31.0556, 'population' => 15000],
            ['name' => 'Сиде', 'region' => 'Средиземноморский регион', 'country' => 'Турция', 'latitude' => 36.7667, 'longitude' => 31.3833, 'population' => 14000],
        ];

        // Объединяем все города
        $allCities = array_merge($russianCities, $turkishCities);

        foreach ($allCities as $city) {
            Location::create(array_merge($city, ['is_active' => true]));
        }
    }
}