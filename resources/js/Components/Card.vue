<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import FavoriteButton from "@/Components/FavoriteButton.vue";
import DistanceBadge from "@/Components/DistanceBadge.vue";

const props = defineProps({
  listing: Object,
  type: String,
  showFavorite: {
    type: Boolean,
    default: true,
  },
});

const formatPrice = (price) => {
  if (!price && price !== 0) return "Цена не указана";
  return new Intl.NumberFormat("ru-RU").format(price) + " ₽";
};

const imageUrl = computed(() => {
  if (props.listing.main_image_url) {
    if (props.listing.main_image_url.startsWith("/storage/")) {
      return props.listing.main_image_url;
    }
    return "/storage/" + props.listing.main_image_url;
  }

  if (props.listing.images && props.listing.images.length > 0) {
    const firstImage = props.listing.images[0];
    if (firstImage.url) {
      return firstImage.url;
    }
    if (firstImage.path) {
      return "/storage/" + firstImage.path;
    }
  }

  return null;
});

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "long",
  });
};
</script>

<template>
  <div
    class="bg-white dark:bg-slate-800 rounded-lg shadow border-2 border-slate-100 overflow-hidden hover:shadow-md transition-shadow relative group"
  >
    <!-- Изображение -->
    <div class="relative h-48 bg-slate-100 overflow-hidden">
      <Link
        :href="route('listing.show', { type, id: listing.id })"
        class="block w-full h-full"
      >
        <img
          v-if="imageUrl"
          :src="imageUrl"
          :alt="listing.title"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <div v-else class="w-full h-full flex items-center justify-center">
          <i class="fa-regular fa-image text-4xl text-slate-300"></i>
        </div>
      </Link>

      <!-- Кнопка избранного -->
      <div v-if="showFavorite" class="absolute top-2 right-2 z-10">
        <FavoriteButton
          :listable-type="type"
          :listable-id="listing.id"
          size="md"
        />
      </div>

      <!-- Бейдж расстояния -->
      <div
        v-if="listing.latitude && listing.longitude"
        class="absolute top-2 left-2 z-10"
      >
        <DistanceBadge
          :latitude="listing.latitude"
          :longitude="listing.longitude"
          size="sm"
        />
      </div>
    </div>

    <!-- Информация -->
    <div class="p-4">
      <Link :href="route('listing.show', { type, id: listing.id })">
        <h3
          class="font-semibold text-slate-800 dark:text-white hover:text-slate-600 transition line-clamp-2 mb-2"
        >
          {{ listing.title }}
        </h3>
      </Link>

      <div class="flex items-center justify-between mb-2">
        <span class="text-xl font-bold text-green-600">
          {{ formatPrice(listing.price) }}
        </span>
      </div>

      <div class="flex items-center justify-between text-sm text-slate-400">
        <span class="flex items-center gap-1">
          <i class="fa-regular fa-location-dot"></i>
          {{ listing.city }}
        </span>
        <DistanceBadge
          v-if="listing.latitude && listing.longitude"
          :latitude="listing.latitude"
          :longitude="listing.longitude"
        />
        <span v-else>
          {{ formatDate(listing.created_at) }}
        </span>
      </div>
    </div>
  </div>
</template>