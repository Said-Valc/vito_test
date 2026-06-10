<script setup>
import MainLayout from "@/Layouts/Main.vue";
import Card from "@/Components/Card.vue";
import FavoriteButton from "@/Components/FavoriteButton.vue";
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";

defineOptions({
  layout: MainLayout,
});

const props = defineProps({
  listings: Array,
  count: Number,
});

const formatPrice = (price) => {
  if (!price && price !== 0) return "Цена не указана";
  return new Intl.NumberFormat("ru-RU").format(price) + " ₽";
};

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const getListingRoute = (listing) => {
  return route("listing.show", {
    type: listing.listing_type,
    id: listing.id,
  });
};
</script>

<template>
  <Head title="Избранное" />

  <div class="py-6">
    <!-- Заголовок -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Избранное</h1>
      <p class="text-slate-500 mt-1">
        {{
          count > 0
            ? `${count} ${getDeclension(count, [
                "объявление",
                "объявления",
                "объявлений",
              ])} в избранном`
            : "Нет избранных объявлений"
        }}
      </p>
    </div>

    <!-- Список избранных объявлений -->
    <div v-if="listings && listings.length > 0">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="listing in listings"
          :key="listing.id"
          class="bg-white rounded-lg shadow border-2 border-slate-100 overflow-hidden hover:shadow-md transition-shadow relative group"
        >
          <!-- Изображение -->
          <Link :href="getListingRoute(listing)" class="block">
            <div class="relative h-48 bg-slate-100 overflow-hidden">
              <img
                v-if="listing.main_image_url"
                :src="listing.main_image_url"
                :alt="listing.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <div
                v-else
                class="w-full h-full flex items-center justify-center"
              >
                <i class="fa-regular fa-image text-4xl text-slate-300"></i>
              </div>

              <!-- Кнопка избранного на изображении -->
              <div class="absolute top-2 right-2">
                <FavoriteButton
                  :listable-type="listing.listing_type"
                  :listable-id="listing.id"
                  size="lg"
                />
              </div>
            </div>
          </Link>

          <!-- Информация -->
          <div class="p-4">
            <Link :href="getListingRoute(listing)">
              <h3
                class="font-semibold text-slate-800 hover:text-red-500 transition line-clamp-2 mb-2"
              >
                {{ listing.title }}
              </h3>
            </Link>

            <div class="flex items-center justify-between mb-2">
              <span class="text-xl font-bold text-green-600">
                {{ formatPrice(listing.price) }}
              </span>
            </div>

            <div
              class="flex items-center justify-between text-sm text-slate-500"
            >
              <span>
                <i class="fa-regular fa-location-dot mr-1"></i>
                {{ listing.city }}
              </span>
              <span>
                <i class="fa-regular fa-clock mr-1"></i>
                {{ formatDate(listing.favorited_at || listing.created_at) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Пустое состояние -->
    <div v-else class="text-center py-16">
      <div class="text-6xl mb-4">
        <i class="fa-regular fa-heart text-slate-200"></i>
      </div>
      <h3 class="text-xl font-semibold text-slate-800 mb-2">
        У вас пока нет избранных объявлений
      </h3>
      <p class="text-slate-500 mb-6">
        Добавляйте объявления в избранное, нажимая на сердечко, чтобы не
        потерять их
      </p>
      <Link
        href="/"
        class="inline-flex items-center px-6 py-3 bg-slate-500 text-white rounded-lg hover:bg-slate-600 transition"
      >
        <i class="fa-regular fa-search mr-2"></i>
        Перейти к объявлениям
      </Link>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    getDeclension(number, words) {
      const cases = [2, 0, 1, 1, 1, 2];
      return words[
        number % 100 > 4 && number % 100 < 20
          ? 2
          : cases[number % 10 < 5 ? number % 10 : 5]
      ];
    },
  },
};
</script>