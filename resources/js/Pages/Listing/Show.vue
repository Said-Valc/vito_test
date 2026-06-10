<script setup>
import MainLayout from "@/Layouts/Main.vue";
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import RatingStars from "@/Components/RatingStars.vue";
import ReviewModal from "@/Components/ReviewModal.vue";
import FavoriteButton from "@/Components/FavoriteButton.vue";
import DistanceBadge from "@/Components/DistanceBadge.vue";

defineOptions({
  layout: MainLayout,
});

const props = defineProps({
  listing: Object,
  type: String,
  images: Array,
});

const page = usePage();

// Состояния для рейтинга
const sellerRating = ref(0);
const sellerRatingCount = ref(0);
const showReviewsModal = ref(false);
const isLoadingRating = ref(true);
const ratingError = ref(null);

// Существующие методы
const formatPrice = (price) => {
  if (!price && price !== 0) return "Цена не указана";
  return new Intl.NumberFormat("ru-RU").format(price) + " ₽";
};

const imageUrls = computed(() => {
  return props.images?.map((img) => img.url) || [];
});

const selectedImage = ref(imageUrls.value[0] || null);

const getTypeName = (type) => {
  const types = {
    auto: "Авто",
    nedvizhimost: "Недвижимость",
    elektronika: "Электроника",
    hobby: "Хобби и отдых",
    rabota: "Работа",
    uslugi: "Услуги",
    lichnie_veschi: "Личные вещи",
    dlya_doma: "Для дома",
  };
  return types[type] || type;
};

// Загрузка рейтинга продавца
const fetchSellerRating = async () => {
  isLoadingRating.value = true;
  ratingError.value = null;

  try {
    const response = await axios.get(
      `/api/v1/sellers/${props.listing.user_id}/rating`
    );
    sellerRating.value = response.data.rating;
    sellerRatingCount.value = response.data.count;
  } catch (error) {
    console.error("Error fetching seller rating:", error);
    ratingError.value = "Не удалось загрузить рейтинг";
    sellerRating.value = 0;
    sellerRatingCount.value = 0;
  } finally {
    isLoadingRating.value = false;
  }
};

const handleReviewAdded = () => {
  fetchSellerRating();
};

const handleReviewDeleted = () => {
  fetchSellerRating();
};

onMounted(() => {
  fetchSellerRating();
});
</script>

<template>
  <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Хлебные крошки -->
    <div class="mb-6 text-sm text-slate-500">
      <Link href="/" class="hover:text-slate-700 transition">Главная</Link>
      <span class="mx-2">/</span>
      <Link
        :href="route('category.list', { type })"
        class="hover:text-slate-700 transition"
      >
        {{ getTypeName(type) }}
      </Link>
      <span class="mx-2">/</span>
      <span class="text-slate-800 font-medium">{{ listing.title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Галерея изображений -->
      <div v-if="imageUrls.length > 0">
        <div
          class="bg-white dark:bg-slate-800 rounded-lg shadow border border-slate-100 overflow-hidden mb-4 relative"
        >
          <img
            :src="selectedImage"
            :alt="listing.title"
            class="w-full h-96 object-contain"
          />
          <!-- Кнопка избранного на изображении с stopPropagation -->
          <div class="absolute top-3 right-3 z-10" @click.stop>
            <FavoriteButton
              :listable-type="type"
              :listable-id="listing.id"
              size="lg"
            />
          </div>
        </div>

        <div class="grid grid-cols-5 gap-2">
          <button
            v-for="(imageUrl, index) in imageUrls"
            :key="index"
            @click="selectedImage = imageUrl"
            class="border-2 rounded-lg overflow-hidden transition-all hover:opacity-80"
            :class="
              selectedImage === imageUrl
                ? 'border-slate-500'
                : 'border-transparent'
            "
          >
            <img
              :src="imageUrl"
              :alt="`Фото ${index + 1}`"
              class="w-full h-20 object-cover"
            />
          </button>
        </div>
      </div>

      <div
        v-else
        class="bg-slate-100 dark:bg-slate-700 rounded-lg h-96 flex items-center justify-center"
      >
        <p class="text-slate-400">Нет фотографий</p>
      </div>

      <!-- Информация об объявлении -->
      <div
        class="bg-white dark:bg-slate-800 rounded-lg shadow border border-slate-100 p-6"
      >
        <div class="flex items-start justify-between mb-4">
          <h1 class="text-2xl font-bold text-slate-800 flex-1">
            {{ listing.title }}
          </h1>
          <!-- Кнопка избранного рядом с заголовком с stopPropagation -->
          <div @click.stop class="ml-4">
            <FavoriteButton
              :listable-type="type"
              :listable-id="listing.id"
              size="lg"
            />
          </div>
        </div>

        <div class="text-3xl font-bold text-green-600 mb-6">
          {{ formatPrice(listing.price) }}
        </div>

        <div class="space-y-4">
          <!-- Локация с расстоянием -->
          <div
            class="flex items-center gap-2 text-slate-600 dark:text-slate-400"
          >
            <i class="fa-regular fa-location-dot w-5"></i>
            <span>{{ listing.city }}</span>
            <DistanceBadge
              v-if="listing.latitude && listing.longitude"
              :latitude="listing.latitude"
              :longitude="listing.longitude"
              size="md"
              class="ml-2"
            />
          </div>

          <div
            class="flex items-center gap-2 text-slate-600 dark:text-slate-400"
          >
            <i class="fa-regular fa-phone w-5"></i>
            <span>{{ listing.phone }}</span>
          </div>

          <!-- Характеристики для авто -->
          <template v-if="type === 'auto'">
            <div class="border-t border-slate-100 pt-4 mt-4">
              <h3 class="font-semibold text-lg mb-3 text-slate-800">
                Характеристики
              </h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="text-sm text-slate-400">Марка</span>
                  <p class="font-medium text-slate-700">
                    {{ listing.brand || "Не указана" }}
                  </p>
                </div>
                <div>
                  <span class="text-sm text-slate-400">Модель</span>
                  <p class="font-medium text-slate-700">
                    {{ listing.model || "Не указана" }}
                  </p>
                </div>
                <div>
                  <span class="text-sm text-slate-400">Год</span>
                  <p class="font-medium text-slate-700">
                    {{ listing.year || "Не указан" }}
                  </p>
                </div>
                <div>
                  <span class="text-sm text-slate-400">Пробег</span>
                  <p class="font-medium text-slate-700">
                    {{
                      listing.mileage ? listing.mileage + " км" : "Не указан"
                    }}
                  </p>
                </div>
                <div v-if="listing.fuel_type">
                  <span class="text-sm text-slate-400">Топливо</span>
                  <p class="font-medium text-slate-700">
                    {{ listing.fuel_type }}
                  </p>
                </div>
                <div v-if="listing.transmission">
                  <span class="text-sm text-slate-400">КПП</span>
                  <p class="font-medium text-slate-700">
                    {{ listing.transmission }}
                  </p>
                </div>
                <div v-if="listing.color">
                  <span class="text-sm text-slate-400">Цвет</span>
                  <p class="font-medium text-slate-700">{{ listing.color }}</p>
                </div>
              </div>
            </div>
          </template>

          <!-- Описание -->
          <div class="border-t border-slate-100 pt-4 mt-4">
            <h3 class="font-semibold text-lg mb-3 text-slate-800">Описание</h3>
            <p class="text-slate-600 dark:text-slate-400 whitespace-pre-line">
              {{ listing.description }}
            </p>
          </div>

          <!-- Информация о продавце с рейтингом -->
          <div class="border-t border-slate-100 pt-4 mt-4">
            <h3 class="font-semibold text-lg mb-3 text-slate-800">
              Информация о продавце
            </h3>
            <div class="flex items-start gap-3">
              <div
                class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold"
              >
                {{ listing.user?.name?.charAt(0) || "?" }}
              </div>

              <div class="flex-1">
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <div>
                    <Link
                      :href="route('seller.profile', { user: listing.user_id })"
                      class="font-medium text-slate-800 dark:text-white hover:text-slate-600 transition"
                    >
                      {{ listing.user?.name || "Пользователь" }}
                    </Link>

                    <div v-if="isLoadingRating" class="mt-1">
                      <div class="flex items-center gap-1">
                        <div
                          class="w-4 h-4 bg-slate-200 rounded animate-pulse"
                        ></div>
                        <div
                          class="w-4 h-4 bg-slate-200 rounded animate-pulse"
                        ></div>
                        <div
                          class="w-4 h-4 bg-slate-200 rounded animate-pulse"
                        ></div>
                        <div
                          class="w-4 h-4 bg-slate-200 rounded animate-pulse"
                        ></div>
                        <div
                          class="w-4 h-4 bg-slate-200 rounded animate-pulse"
                        ></div>
                        <span class="text-sm text-slate-400 ml-1"
                          >Загрузка...</span
                        >
                      </div>
                    </div>

                    <div v-else-if="ratingError" class="mt-1">
                      <span class="text-xs text-slate-400"
                        >Рейтинг временно недоступен</span
                      >
                    </div>

                    <RatingStars
                      v-else
                      :rating="sellerRating"
                      :count="sellerRatingCount"
                      :show-empty="true"
                      class="mt-1"
                    />
                  </div>

                  <!-- КНОПКА "НАПИСАТЬ" -->
                  <Link
                    :href="
                      route('chat.show', listing.user_id) +
                      '?listing_id=' +
                      listing.id +
                      '&listing_type=' +
                      type
                    "
                    class="inline-flex items-center px-4 py-2 bg-slate-500 text-white rounded-lg hover:bg-slate-600 transition"
                  >
                    <i class="fa-regular fa-envelope mr-2"></i>
                    Написать
                  </Link>
                </div>

                <p class="text-sm text-slate-400 mt-2">
                  На сайте с
                  {{ new Date(listing.user?.created_at).toLocaleDateString() }}
                </p>

                <button
                  @click="showReviewsModal = true"
                  v-if="sellerRatingCount > 0 && !isLoadingRating"
                  class="text-sm text-slate-500 hover:text-slate-700 mt-2 transition"
                >
                  Посмотреть все отзывы ({{ sellerRatingCount }})
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ReviewModal
      v-model:show="showReviewsModal"
      :seller-id="listing.user_id"
      :current-user-id="page.props.auth.user?.id"
      :listing-type="type"
      :listing-id="listing.id"
      @review-added="handleReviewAdded"
      @review-deleted="handleReviewDeleted"
    />
  </div>
</template>