<script setup>
import Card from "../Components/Card.vue";
import CategoryGrid from "../Components/CategoryGrid.vue";
import DistanceBadge from "../Components/DistanceBadge.vue";
import { Link, Head } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useGeolocation } from "@/composables/useGeolocation";

const params = route().params;

const props = defineProps({
  listings: Array,
  searchTerm: String,
});

const {
  userLocation,
  calculateDistance,
  init: initGeolocation,
} = useGeolocation();

// Количество отображаемых объявлений
const perPage = 20;
const visibleCount = ref(perPage);
const isLoading = ref(false);
const showNearbyOnly = ref(false);
const searchRadius = ref(50); // км

// Все объявления
const allListings = computed(() => {
  if (!props.listings) return [];
  return props.listings;
});

// Фильтрация по расстоянию
const filteredListings = computed(() => {
  if (!showNearbyOnly.value || !userLocation.value) {
    return allListings.value;
  }

  return allListings.value
    .filter((listing) => {
      if (!listing.latitude || !listing.longitude) return false;

      const distance = calculateDistance(
        userLocation.value.latitude,
        userLocation.value.longitude,
        Number(listing.latitude),
        Number(listing.longitude)
      );

      listing._distance = distance;
      return distance !== null && distance <= searchRadius.value;
    })
    .sort((a, b) => (a._distance || 0) - (b._distance || 0));
});

// Видимые объявления
const visibleListings = computed(() => {
  return filteredListings.value.slice(0, visibleCount.value);
});

// Есть ли еще объявления для загрузки
const hasMore = computed(() => {
  return visibleCount.value < filteredListings.value.length;
});

const username = params.user_id
  ? allListings.value.find((i) => i.user_id === Number(params.user_id))?.user
      ?.name
  : null;

const getListingType = (listing) => {
  if (listing.listing_type) return listing.listing_type;

  if (listing.brand !== undefined) {
    if (listing.fuel_type !== undefined || listing.transmission !== undefined) {
      return "auto";
    }
    return "elektronika";
  }
  if (listing.rooms !== undefined) return "nedvizhimost";
  if (listing.company_name !== undefined) return "vacancy";
  if (listing.full_name !== undefined) return "resume";
  if (listing.service_type !== undefined) return "uslugi";
  return "other";
};

// Загрузка еще объявлений
const loadMore = () => {
  if (isLoading.value || !hasMore.value) return;

  isLoading.value = true;

  setTimeout(() => {
    visibleCount.value += perPage;
    isLoading.value = false;
  }, 300);
};

// Бесконечный скролл
const handleScroll = () => {
  const scrollHeight = document.documentElement.scrollHeight;
  const scrollTop = window.scrollY;
  const clientHeight = window.innerHeight;

  if (scrollHeight - scrollTop - clientHeight < 200) {
    loadMore();
  }
};

onMounted(() => {
  initGeolocation();
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
  <Head title="- Последние объявления" />

  <!-- Сетка категорий -->
  <CategoryGrid />

  <!-- Фильтры -->
  <div
    v-if="params.search || params.tag || params.user_id"
    class="flex items-center gap-2 mb-6 flex-wrap"
  >
    <Link
      class="px-3 py-1.5 rounded-md bg-slate-500 text-white flex items-center gap-2 text-sm"
      v-if="params.tag"
      :href="route('home', { ...params, tag: null })"
    >
      {{ params.tag }}
      <i class="fa-solid fa-xmark text-xs"></i>
    </Link>

    <Link
      class="px-3 py-1.5 rounded-md bg-slate-500 text-white flex items-center gap-2 text-sm"
      v-if="params.search"
      :href="route('home', { ...params, search: null })"
    >
      {{ params.search }}
      <i class="fa-solid fa-xmark text-xs"></i>
    </Link>

    <Link
      class="px-3 py-1.5 rounded-md bg-slate-500 text-white flex items-center gap-2 text-sm"
      v-if="params.user_id && username"
      :href="route('home', { ...params, user_id: null })"
    >
      {{ username }}
      <i class="fa-solid fa-xmark text-xs"></i>
    </Link>
  </div>

  <!-- Кнопка "Рядом со мной" -->
  <div v-if="userLocation" class="mb-4 flex items-center gap-2">
    <button
      @click="showNearbyOnly = !showNearbyOnly"
      :class="[
        'inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition',
        showNearbyOnly
          ? 'bg-green-500 text-white'
          : 'bg-white text-slate-600 border-2 border-slate-200 hover:border-green-300',
      ]"
    >
      <i class="fa-solid fa-location-crosshairs"></i>
      <span v-if="showNearbyOnly">
        Показано в радиусе {{ searchRadius }} км
      </span>
      <span v-else> Показать рядом со мной </span>
    </button>

    <!-- Выбор радиуса -->
    <select
      v-if="showNearbyOnly"
      v-model="searchRadius"
      class="px-3 py-2 border-2 border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
    >
      <option :value="1">1 км</option>
      <option :value="5">5 км</option>
      <option :value="10">10 км</option>
      <option :value="25">25 км</option>
      <option :value="50">50 км</option>
      <option :value="100">100 км</option>
    </select>
  </div>

  <!-- Объявления -->
  <div v-if="visibleListings.length > 0">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold text-slate-800">
        {{
          params.search || params.tag || params.user_id
            ? "Результаты поиска"
            : showNearbyOnly
            ? "Объявления рядом"
            : "Все объявления"
        }}
      </h2>
    </div>

    <div
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
    >
      <div
        v-for="listing in visibleListings"
        :key="listing.id + '_' + getListingType(listing)"
      >
        <Card :listing="listing" :type="getListingType(listing)" />
      </div>
    </div>

    <!-- Индикатор загрузки -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="flex items-center gap-2 text-slate-500">
        <i class="fa-solid fa-spinner fa-spin text-xl"></i>
        <span class="text-sm">Загрузка...</span>
      </div>
    </div>

    <!-- Кнопка "Загрузить еще" -->
    <div v-if="hasMore && !isLoading" class="flex justify-center py-8">
      <button
        @click="loadMore"
        class="px-6 py-3 bg-white text-slate-500 border-2 border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200 font-medium"
      >
        <i class="fa-solid fa-arrow-down mr-2"></i>
        Показать еще ({{ filteredListings.length - visibleListings.length }})
      </button>
    </div>

    <!-- Все объявления загружены -->
    <div v-if="!hasMore && visibleListings.length > 0" class="text-center py-8">
      <p class="text-slate-400 text-sm">Все объявления загружены</p>
    </div>
  </div>

  <div v-else class="text-center py-12">
    <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i>
    <p class="text-slate-500 text-lg">
      {{ showNearbyOnly ? "Нет объявлений поблизости" : "Объявлений пока нет" }}
    </p>
    <p class="text-slate-400 text-sm mt-1">
      {{
        showNearbyOnly
          ? "Попробуйте увеличить радиус поиска"
          : "Станьте первым, кто разместит объявление!"
      }}
    </p>
  </div>
</template>