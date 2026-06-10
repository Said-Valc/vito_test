<script setup>
import MainLayout from "@/Layouts/Main.vue";
import Card from "@/Components/Card.vue";
import FavoriteButton from "@/Components/FavoriteButton.vue";
import { Link } from "@inertiajs/vue3";

defineOptions({
  layout: MainLayout,
});

const props = defineProps({
  type: String,
  categoryName: String,
  listings: [Array, Object],
  subcategories: Array,
});

const formatPrice = (price) => {
  return new Intl.NumberFormat("ru-RU").format(price) + " ₽";
};

const categoryIcons = {
  auto: "fa-car",
  nedvizhimost: "fa-building",
  rabota: "fa-briefcase",
  uslugi: "fa-handshake",
  lichnie_veschi: "fa-shirt",
  dlya_doma: "fa-home",
  elektronika: "fa-laptop",
  hobby: "fa-futbol",
};
</script>

<template>
  <!-- Хлебные крошки -->
  <div class="mb-6 text-sm text-slate-500">
    <Link href="/" class="hover:text-slate-700 transition">Главная</Link>
    <span class="mx-2">/</span>
    <span class="text-slate-800 font-medium">{{ categoryName }}</span>
  </div>

  <div class="flex gap-6">
    <!-- Сайдбар с подкатегориями -->
    <div class="w-64 shrink-0">
      <div class="bg-white rounded-lg shadow border-2 border-slate-100 p-4">
        <h2
          class="font-semibold text-lg mb-4 flex items-center gap-2 text-slate-800"
        >
          <i
            :class="['fa-solid', categoryIcons[type]]"
            class="text-slate-500"
          ></i>
          {{ categoryName }}
        </h2>
        <div class="space-y-1">
          <Link
            v-for="sub in subcategories"
            :key="sub.id"
            :href="route('category.show', { type, id: sub.id })"
            class="block px-3 py-2 rounded-md hover:bg-slate-50 transition-colors text-slate-600 hover:text-slate-800"
          >
            <div class="flex items-center justify-between">
              <span>{{ sub.name }}</span>
              <span
                v-if="sub.available_actions || sub.action"
                class="text-xs text-slate-400"
              >
                {{ sub.available_actions?.[0] || sub.action }}
              </span>
            </div>
          </Link>
        </div>
      </div>
    </div>

    <!-- Список объявлений -->
    <div class="flex-1">
      <h1 class="text-2xl font-bold mb-6 text-slate-800">{{ categoryName }}</h1>

      <!-- Для работы показываем и вакансии и резюме -->
      <template v-if="type === 'rabota'">
        <div class="mb-8">
          <h2 class="text-xl font-semibold mb-4 text-slate-800">Вакансии</h2>
          <div v-if="listings.vacancies?.length" class="space-y-4">
            <div
              v-for="vacancy in listings.vacancies"
              :key="vacancy.id"
              class="bg-white rounded-lg shadow border-2 border-slate-100 p-6 hover:shadow-md transition-shadow relative"
            >
              <div class="absolute top-3 right-3 z-10" @click.stop>
                <FavoriteButton
                  :listable-type="'vacancy'"
                  :listable-id="vacancy.id"
                  size="sm"
                />
              </div>
              <Link
                :href="
                  route('listing.show', { type: 'vacancy', id: vacancy.id })
                "
              >
                <h3 class="text-lg font-semibold text-slate-800 pr-8">
                  {{ vacancy.title }}
                </h3>
                <p class="text-slate-500 mt-2">
                  {{ vacancy.company_name }}
                </p>
                <div class="flex justify-between items-center mt-4">
                  <span class="text-green-600 font-bold"
                    >{{ formatPrice(vacancy.salary_from) }} -
                    {{ formatPrice(vacancy.salary_to) }}</span
                  >
                  <span class="text-sm text-slate-400">{{ vacancy.city }}</span>
                </div>
              </Link>
            </div>
          </div>
          <p v-else class="text-slate-400">Нет активных вакансий</p>
        </div>

        <div>
          <h2 class="text-xl font-semibold mb-4 text-slate-800">Резюме</h2>
          <div v-if="listings.resumes?.length" class="space-y-4">
            <div
              v-for="resume in listings.resumes"
              :key="resume.id"
              class="bg-white rounded-lg shadow border-2 border-slate-100 p-6 hover:shadow-md transition-shadow relative"
            >
              <div class="absolute top-3 right-3 z-10" @click.stop>
                <FavoriteButton
                  :listable-type="'resume'"
                  :listable-id="resume.id"
                  size="sm"
                />
              </div>
              <Link
                :href="route('listing.show', { type: 'resume', id: resume.id })"
              >
                <h3 class="text-lg font-semibold text-slate-800 pr-8">
                  {{ resume.title }}
                </h3>
                <p class="text-slate-500 mt-2">
                  {{ resume.full_name }}, {{ resume.age }} лет
                </p>
                <div class="flex justify-between items-center mt-4">
                  <span class="text-green-600 font-bold">{{
                    formatPrice(resume.salary_expectation)
                  }}</span>
                  <span class="text-sm text-slate-400">{{ resume.city }}</span>
                </div>
              </Link>
            </div>
          </div>
          <p v-else class="text-slate-400">Нет активных резюме</p>
        </div>
      </template>

      <!-- Для остальных категорий используем Card -->
      <template v-else>
        <div
          v-if="listings.data?.length"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
          <div
            v-for="listing in listings.data"
            :key="listing.id"
            class="relative"
          >
            <Card :listing="listing" :type="type" />
            <!-- Кнопка уже внутри Card, но если нужно снаружи - добавляем stop -->
          </div>
        </div>
        <p v-else class="text-slate-400 text-center py-8">
          В этой категории пока нет объявлений
        </p>
      </template>
    </div>
  </div>
</template>