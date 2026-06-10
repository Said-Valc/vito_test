<script setup>
import MainLayout from "@/Layouts/Main.vue";
import { ref, computed } from "vue";
import { Link, usePage, Head } from "@inertiajs/vue3";
import axios from "axios";
import RatingStars from "@/Components/RatingStars.vue";
import ReviewModal from "@/Components/ReviewModal.vue";
import SellerListingsTab from "@/Components/SellerListingsTab.vue";
import UpdateInfo from "./Sections/UpdateInfo.vue";
import UpdatePassword from "./Sections/UpdatePassword.vue";
import DeleteAccount from "./Sections/DeleteAccount.vue";

defineOptions({
  layout: MainLayout,
});

const page = usePage();

const props = defineProps({
  user: Object,
  seller: Object,
  activeListings: Array,
  completedListings: Array,
  completedCount: Number,
  status: String,
});

const currentUser = computed(() => page.props.auth.user);
const isOwnProfile = computed(() => currentUser.value?.id === props.seller.id);

const activeTab = ref("active");
const showReviewsModal = ref(false);

const sellerRating = ref(props.seller.rating || 0);
const sellerRatingCount = ref(props.seller.rating_count || 0);

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const handleReviewAdded = async (data) => {
  console.log("Review added event", data);

  try {
    const response = await axios.get(
      `/api/v1/sellers/${props.seller.id}/rating`
    );
    console.log("New rating data:", response.data);

    sellerRating.value = response.data.rating;
    sellerRatingCount.value = response.data.count;

    props.seller.rating = response.data.rating;
    props.seller.rating_count = response.data.count;
  } catch (error) {
    console.error("Error updating rating:", error);
  }
};

const tabs = computed(() => {
  const items = [
    {
      id: "active",
      label: "Активные объявления",
      icon: "fa-eye",
      count: props.activeListings.length,
    },
    {
      id: "completed",
      label: "Завершенные объявления",
      icon: "fa-circle-check",
      count: props.completedListings.length,
    },
    {
      id: "reviews",
      label: "Отзывы",
      icon: "fa-star",
      count: sellerRatingCount.value,
      isButton: true,
    },
  ];

  if (isOwnProfile.value) {
    items.push({ id: "settings", label: "Настройки", icon: "fa-gear" });
  }

  return items;
});

const handleTabClick = (tabId) => {
  if (tabId === "reviews") {
    showReviewsModal.value = true;
  } else {
    activeTab.value = tabId;
  }
};

const getDealWord = (count) => {
  if (count % 10 === 1 && count % 100 !== 11) {
    return "завершенная сделка";
  } else if (
    count % 10 >= 2 &&
    count % 10 <= 4 &&
    (count % 100 < 10 || count % 100 >= 20)
  ) {
    return "завершенные сделки";
  } else {
    return "завершенных сделок";
  }
};
</script>

<template>
  <Head :title="`${seller.name} - Профиль`" />

  <div class="py-6">
    <!-- Шапка профиля -->
    <div
      class="bg-white rounded-lg shadow border-2 border-slate-100 overflow-hidden mb-8"
    >
      <!-- Баннер/Обложка -->
      <div class="h-32 bg-gradient-to-r from-slate-500 to-slate-600"></div>

      <!-- Информация о продавце -->
      <div class="px-6 pb-6">
        <!-- Аватар и основная информация -->
        <div
          class="flex flex-col md:flex-row items-start md:items-end -mt-12 mb-4"
        >
          <div class="relative">
            <div class="w-24 h-24 rounded-full bg-white p-1 shadow-lg">
              <div
                class="w-full h-full rounded-full bg-gradient-to-r from-slate-500 to-slate-600 flex items-center justify-center"
              >
                <span class="text-3xl font-bold text-white">
                  {{ seller.name.charAt(0).toUpperCase() }}
                </span>
              </div>
            </div>
            <!-- Бейдж "Это вы" -->
            <span
              v-if="isOwnProfile"
              class="absolute -bottom-1 -right-1 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full border-2 border-white"
            >
              Вы
            </span>
          </div>

          <div class="flex-1 mt-4 md:mt-0 md:ml-6 text-center md:text-left">
            <div
              class="flex items-center gap-3 flex-wrap justify-center md:justify-start"
            >
              <h1 class="text-2xl font-bold text-slate-800">
                {{ seller.name }}
              </h1>
              <span class="text-sm text-slate-500">
                {{ seller.email }}
              </span>
            </div>

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
              <div class="flex items-center gap-1 text-slate-500">
                <i class="fa-regular fa-circle-check"></i>
                <span class="text-sm">
                  {{ completedCount }} {{ getDealWord(completedCount) }}
                </span>
              </div>

              <!-- Дата регистрации -->
              <div class="flex items-center gap-1 text-slate-500">
                <i class="fa-regular fa-calendar"></i>
                <span class="text-sm">
                  На сайте с {{ formatDate(seller.created_at) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Кнопка "Написать сообщение" -->
          <Link
            v-if="!isOwnProfile"
            :href="route('chat.show', seller.id)"
            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-slate-500 text-white rounded-lg hover:bg-slate-600 transition"
          >
            <i class="fa-regular fa-envelope mr-2"></i>
            Написать сообщение
          </Link>
        </div>

        <!-- Статистика -->
        <div
          class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100"
        >
          <div class="text-center">
            <div class="text-2xl font-bold text-slate-800">
              {{ activeListings.length }}
            </div>
            <div class="text-sm text-slate-500">Активных объявлений</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-slate-800">
              {{ completedCount }}
            </div>
            <div class="text-sm text-slate-500">Завершенных сделок</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-slate-800">
              {{ sellerRatingCount }}
            </div>
            <div class="text-sm text-slate-500">Отзывов</div>
          </div>
          <div class="text-center">
            <div
              class="text-2xl font-bold text-slate-800"
              v-if="sellerRatingCount > 0"
            >
              {{ Math.round(sellerRating * 20) }}%
            </div>
            <div class="text-2xl font-bold text-slate-800" v-else>—</div>
            <div class="text-sm text-slate-500">Положительных</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Вкладки -->
    <div
      class="bg-white rounded-lg shadow border-2 border-slate-100 overflow-hidden"
    >
      <div class="border-b border-slate-100">
        <nav class="flex space-x-1 px-2 overflow-x-auto" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="handleTabClick(tab.id)"
            :class="[
              'py-4 px-4 border-b-2 font-medium text-sm transition whitespace-nowrap',
              (tab.id === activeTab && !tab.isButton) ||
              (tab.id === 'reviews' && showReviewsModal)
                ? 'border-slate-500 text-slate-600'
                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
            ]"
          >
            <i :class="['fa-regular', tab.icon, 'mr-2']"></i>
            {{ tab.label }}
            <span
              v-if="tab.count !== undefined"
              class="ml-2 px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600"
            >
              {{ tab.count }}
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

        <!-- Настройки профиля -->
        <div v-else-if="activeTab === 'settings' && isOwnProfile && user">
          <div class="max-w-3xl mx-auto space-y-6">
            <UpdateInfo :user="user" :status="status" />
            <UpdatePassword />
            <DeleteAccount />
          </div>
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