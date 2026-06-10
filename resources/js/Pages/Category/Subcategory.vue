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
  subcategory: Object,
  listings: Object,
  subcategories: Array,
});

const formatPrice = (price) => {
  return new Intl.NumberFormat("ru-RU").format(price) + " ₽";
};

// Иконки для категорий
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

    <!-- Ссылка на все объявления категории -->
    <Link
      :href="route('category.list', { type })"
      class="hover:text-slate-700 transition"
    >
      {{ categoryName }}
    </Link>

    <span class="mx-2">/</span>
    <span class="text-slate-800">{{ subcategory.name }}</span>
  </div>

  <div class="flex gap-6">
    <!-- Сайдбар с подкатегориями (как в Index.vue) -->
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
            class="block px-3 py-2 rounded-md transition-colors"
            :class="[
              sub.id === subcategory.id
                ? 'bg-slate-100 text-slate-800 font-medium'
                : 'hover:bg-slate-50 text-slate-600 hover:text-slate-800',
            ]"
          >
            <div class="flex items-center justify-between">
              <span>{{ sub.name }}</span>
              <span
                v-if="sub.available_actions || sub.action"
                class="text-xs"
                :class="
                  sub.id === subcategory.id
                    ? 'text-slate-500'
                    : 'text-slate-400'
                "
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
      <h1 class="text-2xl font-bold mb-6 text-slate-800">
        {{ subcategory.name }}
      </h1>

      <!-- Список объявлений подкатегории -->
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
          <!-- Кнопка уже внутри Card -->
        </div>
      </div>

      <div v-else class="text-center py-12">
        <p class="text-slate-400">В этой подкатегории пока нет объявлений</p>
      </div>
    </div>
  </div>
</template>