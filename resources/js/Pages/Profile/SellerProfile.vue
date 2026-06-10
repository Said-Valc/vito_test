<script setup>
import MainLayout from "@/Layouts/Main.vue";
import { ref, computed, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import RatingStars from "@/Components/RatingStars.vue";
import ReviewModal from "@/Components/ReviewModal.vue";
import SellerListingsTab from "@/Components/SellerListingsTab.vue";
import { usePage } from "@inertiajs/vue3";

defineOptions({
  layout: MainLayout,
});

const page = usePage();
const props = defineProps({
  seller: Object,
  activeListings: Array,
  completedListings: Array,
  completedCount: Number,
});

const currentUser = computed(() => page.props.auth.user);
const activeTab = ref("active");
const showReviewsModal = ref(false);

// Данные для рейтинга (загружаем через API для актуальности)
const sellerRating = ref(props.seller.rating || 0);
const sellerRatingCount = ref(props.seller.rating_count || 0);
const isLoadingRating = ref(false);

// Форматирование даты
const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

// Обновление рейтинга после добавления/удаления отзыва
const handleReviewAdded = () => {
  axios
    .get(`/api/v1/sellers/${props.seller.id}/rating`)
    .then((response) => {
      sellerRating.value = response.data.rating;
      sellerRatingCount.value = response.data.count;
    })
    .catch((error) => {
      console.error("Error updating rating:", error);
    });
};
</script>

<template>
  <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Шапка профиля -->
    <div
      class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-8"
    >
      <!-- Баннер/Обложка -->
      <div class="h-32 bg-gradient-to-r from-blue-500 to-purple-600"></div>

      <!-- Информация о продавце -->
      <div class="px-6 pb-6">
        <!-- Аватар -->
        <div
          class="flex flex-col md:flex-row items-start md:items-end -mt-12 mb-4"
        >
          <div class="relative">
            <div
              class="w-24 h-24 rounded-full bg-white dark:bg-gray-800 p-1 shadow-lg"
            >
              <div
                class="w-full h-full rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center"
              >
                <span class="text-3xl font-bold text-white">
                  {{ seller.name.charAt(0).toUpperCase() }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex-1 mt-4 md:mt-0 md:ml-6 text-center md:text-left">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ seller.name }}
            </h1>

            <div
              class="flex flex-wrap items-center gap-4 mt-2 justify-center md:justify-start"
            >
              <!-- Рейтинг -->
              <div class="flex items-center gap-2">
                <RatingStars
                  :rating="sellerRating"
                  :count="sellerRatingCount"
                  :show-empty="true"
                  :size="16"
                />
              </div>

              <!-- Завершенные сделки -->
              <div
                class="flex items-center gap-1 text-gray-500 dark:text-gray-400"
              >
                <i class="fa-regular fa-circle-check"></i>
                <span class="text-sm">
                  {{ completedCount }}
                  {{
                    completedCount % 10 === 1 && completedCount % 100 !== 11
                      ? "завершенная сделка"
                      : completedCount % 10 >= 2 &&
                        completedCount % 10 <= 4 &&
                        (completedCount % 100 < 10 ||
                          completedCount % 100 >= 20)
                      ? "завершенные сделки"
                      : "завершенных сделок"
                  }}
                </span>
              </div>

              <!-- Дата регистрации -->
              <div
                class="flex items-center gap-1 text-gray-500 dark:text-gray-400"
              >
                <i class="fa-regular fa-calendar"></i>
                <span class="text-sm">
                  На сайте с {{ formatDate(seller.created_at) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Кнопка "Написать сообщение" для других пользователей -->
          <Link
            v-if="currentUser && currentUser.id !== seller.id"
            :href="route('chat.show', seller.id)"
            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <i class="fa-regular fa-envelope mr-2"></i>
            Написать сообщение
          </Link>
        </div>

        <!-- Статистика -->
        <div
          class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t dark:border-gray-700"
        >
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ activeListings.length }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Активных объявлений
            </div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ completedCount }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Завершенных сделок
            </div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ sellerRatingCount }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Отзывов</div>
          </div>
          <div class="text-center">
            <div
              class="text-2xl font-bold text-gray-900 dark:text-white"
              v-if="sellerRatingCount > 0"
            >
              {{ Math.round(sellerRating * 20) }}%
            </div>
            <div
              class="text-2xl font-bold text-gray-900 dark:text-white"
              v-else
            >
              —
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Положительных
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Вкладки -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
      <div class="border-b dark:border-gray-700">
        <nav class="flex space-x-8 px-6" aria-label="Tabs">
          <button
            @click="activeTab = 'active'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition',
              activeTab === 'active'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
            ]"
          >
            <i class="fa-regular fa-eye mr-2"></i>
            Активные объявления
            <span
              class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700"
            >
              {{ activeListings.length }}
            </span>
          </button>

          <button
            @click="activeTab = 'completed'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition',
              activeTab === 'completed'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
            ]"
          >
            <i class="fa-regular fa-circle-check mr-2"></i>
            Завершенные объявления
            <span
              class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700"
            >
              {{ completedListings.length }}
            </span>
          </button>

          <button
            @click="showReviewsModal = true"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition',
              'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
            ]"
          >
            <i class="fa-regular fa-star mr-2"></i>
            Отзывы
            <span
              class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700"
            >
              {{ sellerRatingCount }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Контент вкладок -->
      <div class="p-6">
        <!-- Активные объявления -->
        <div v-if="activeTab === 'active'">
          <SellerListingsTab
            :listings="activeListings"
            :seller-id="seller.id"
            type="active"
          />
        </div>

        <!-- Завершенные объявления -->
        <div v-else-if="activeTab === 'completed'">
          <SellerListingsTab
            :listings="completedListings"
            :seller-id="seller.id"
            type="completed"
          />
        </div>
      </div>
    </div>

    <!-- Модальное окно с отзывами -->
    <ReviewModal
      v-model:show="showReviewsModal"
      :seller-id="seller.id"
      :current-user-id="currentUser?.id"
      @review-added="handleReviewAdded"
      @review-deleted="handleReviewAdded"
    />
  </div>
</template>