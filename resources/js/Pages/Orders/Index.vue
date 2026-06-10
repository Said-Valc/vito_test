<template>
  <div class="py-6">
    <!-- Заголовок -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Мои сделки</h1>
      <p class="text-slate-500 mt-1">
        Управляйте вашими сделками и отслеживайте их статус
      </p>
    </div>

    <!-- Фильтры -->
    <div class="bg-white rounded-lg shadow border-2 border-blue-100 p-4 mb-6">
      <div class="flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Роль в сделке
          </label>
          <select
            v-model="filters.role"
            @change="applyFilters"
            class="w-full px-3 py-2 border-2 border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white text-slate-800"
          >
            <option value="all">Все сделки</option>
            <option value="buyer">Я покупатель</option>
            <option value="seller">Я продавец</option>
          </select>
        </div>

        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Статус сделки
          </label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full px-3 py-2 border-2 border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white text-slate-800"
          >
            <option value="all">Все статусы</option>
            <option value="pending">Ожидает начала</option>
            <option value="active">Активные</option>
            <option value="completed">Завершенные</option>
            <option value="cancelled">Отмененные</option>
          </select>
        </div>

        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="px-4 py-2 text-slate-500 hover:text-slate-700 transition"
          >
            <i class="fa-regular fa-rotate-right mr-2"></i>
            Сбросить
          </button>
        </div>
      </div>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow border-2 border-blue-100 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-500">Всего сделок</p>
            <p class="text-2xl font-bold text-slate-800">
              {{ stats.total }}
            </p>
          </div>
          <div
            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center"
          >
            <i class="fa-regular fa-handshake text-blue-500"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow border-2 border-blue-100 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-500">Ожидают</p>
            <p class="text-2xl font-bold text-yellow-600">
              {{ stats.pending }}
            </p>
          </div>
          <div
            class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center"
          >
            <i class="fa-regular fa-clock text-yellow-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow border-2 border-blue-100 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-500">Активные</p>
            <p class="text-2xl font-bold text-blue-600">{{ stats.active }}</p>
          </div>
          <div
            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center"
          >
            <i class="fa-regular fa-play text-blue-500"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow border-2 border-blue-100 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-500">Завершено</p>
            <p class="text-2xl font-bold text-green-600">
              {{ stats.completed }}
            </p>
          </div>
          <div
            class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center"
          >
            <i class="fa-regular fa-circle-check text-green-600"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Список сделок -->
    <div v-if="orders.data && orders.data.length > 0" class="space-y-4">
      <div
        v-for="order in orders.data"
        :key="order.id"
        class="bg-white rounded-lg shadow border-2 border-blue-100 overflow-hidden"
      >
        <div class="p-6">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <!-- Заголовок и статус -->
              <div class="flex flex-wrap items-center gap-3 mb-3">
                <span
                  class="px-3 py-1 rounded-full text-sm font-medium"
                  :class="getStatusClass(order.status)"
                >
                  <i
                    class="fa-regular mr-1"
                    :class="getStatusIcon(order.status)"
                  ></i>
                  {{ getStatusText(order.status) }}
                </span>
                <span class="text-sm text-slate-500">
                  Сделка #{{ order.id }}
                </span>
                <span class="text-sm text-slate-500">
                  от {{ formatDate(order.created_at) }}
                </span>
              </div>

              <!-- Информация о товаре -->
              <div class="flex gap-4 mb-4">
                <img
                  v-if="getListingImage(order)"
                  :src="getListingImage(order)"
                  :alt="order.listable?.title"
                  class="w-24 h-24 object-cover rounded-lg"
                />
                <div
                  v-else
                  class="w-24 h-24 bg-slate-100 rounded-lg flex items-center justify-center"
                >
                  <i class="fa-regular fa-image text-slate-400 text-2xl"></i>
                </div>

                <div class="flex-1">
                  <Link
                    :href="
                      route('listing.show', {
                        type: getListingType(order.listable_type),
                        id: order.listable_id,
                      })
                    "
                    class="font-semibold text-lg text-slate-800 hover:text-blue-600 transition"
                  >
                    {{ order.listable?.title || "Объявление удалено" }}
                  </Link>
                  <p class="text-blue-600 font-bold text-xl mt-1">
                    {{ formatPrice(order.amount || order.listable?.price) }}
                  </p>
                </div>
              </div>

              <!-- Участники сделки -->
              <div
                class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 bg-blue-50/30 rounded-lg"
              >
                <div>
                  <p class="text-sm text-slate-500 mb-1">Продавец</p>
                  <div class="flex items-center gap-2">
                    <div
                      class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center"
                    >
                      <span class="text-green-600 font-semibold">
                        {{ order.seller?.name?.charAt(0).toUpperCase() || "?" }}
                      </span>
                    </div>
                    <div>
                      <Link
                        :href="
                          route('seller.profile', { user: order.seller_id })
                        "
                        class="font-medium text-slate-800 hover:text-blue-600"
                      >
                        {{ order.seller?.name || "Пользователь" }}
                      </Link>
                      <span
                        v-if="order.seller_confirmed_at"
                        class="ml-2 text-xs text-green-600"
                      >
                        <i class="fa-regular fa-check-circle"></i> Отправил
                      </span>
                    </div>
                  </div>
                </div>

                <div>
                  <p class="text-sm text-slate-500 mb-1">Покупатель</p>
                  <div class="flex items-center gap-2">
                    <div
                      class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"
                    >
                      <span class="text-blue-600 font-semibold">
                        {{ order.buyer?.name?.charAt(0).toUpperCase() || "?" }}
                      </span>
                    </div>
                    <div>
                      <Link
                        :href="
                          route('seller.profile', { user: order.buyer_id })
                        "
                        class="font-medium text-slate-800 hover:text-blue-600"
                      >
                        {{ order.buyer?.name || "Пользователь" }}
                      </Link>
                      <span
                        v-if="order.buyer_confirmed_at"
                        class="ml-2 text-xs text-green-600"
                      >
                        <i class="fa-regular fa-check-circle"></i> Получил
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Прогресс сделки -->
              <div v-if="order.status === 'active'" class="mb-4">
                <div class="flex justify-between text-sm mb-2">
                  <span
                    class="flex items-center gap-1"
                    :class="{
                      'text-green-600': order.seller_confirmed_at,
                      'text-slate-400': !order.seller_confirmed_at,
                    }"
                  >
                    <i
                      class="fa-regular"
                      :class="
                        order.seller_confirmed_at
                          ? 'fa-check-circle'
                          : 'fa-circle'
                      "
                    ></i>
                    Продавец отправил
                  </span>
                  <span
                    class="flex items-center gap-1"
                    :class="{
                      'text-green-600': order.buyer_confirmed_at,
                      'text-slate-400': !order.buyer_confirmed_at,
                    }"
                  >
                    <i
                      class="fa-regular"
                      :class="
                        order.buyer_confirmed_at
                          ? 'fa-check-circle'
                          : 'fa-circle'
                      "
                    ></i>
                    Покупатель получил
                  </span>
                </div>
                <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-green-500 transition-all duration-500"
                    :style="{
                      width:
                        (order.seller_confirmed_at ? 50 : 0) +
                        (order.buyer_confirmed_at ? 50 : 0) +
                        '%',
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Кнопки действий -->
          <div class="flex flex-wrap gap-2 pt-4 border-t border-blue-100">
            <!-- Информация о следующем шаге -->
            <div
              v-if="getNextStepInfo(order)"
              class="w-full mb-2 text-sm"
              :class="getNextStepInfo(order).class"
            >
              <i class="fa-regular fa-info-circle mr-1"></i>
              {{ getNextStepInfo(order).text }}
            </div>

            <!-- Начать сделку (только для продавца) -->
            <button
              v-if="canActivate(order)"
              @click="activateOrder(order)"
              :disabled="loading === order.id"
              class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition disabled:opacity-50 flex items-center gap-2"
            >
              <i
                v-if="loading === order.id"
                class="fa-regular fa-spinner fa-spin"
              ></i>
              <i v-else class="fa-regular fa-play"></i>
              Начать сделку
            </button>

            <!-- Подтвердить отправку (только для продавца) -->
            <button
              v-if="canConfirmSeller(order)"
              @click="confirmOrder(order, 'seller')"
              :disabled="loading === order.id"
              class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition disabled:opacity-50 flex items-center gap-2"
            >
              <i
                v-if="loading === order.id"
                class="fa-regular fa-spinner fa-spin"
              ></i>
              <i v-else class="fa-regular fa-paper-plane"></i>
              Подтвердить отправку
            </button>

            <!-- Подтвердить получение (только для покупателя) -->
            <button
              v-if="canConfirmBuyer(order)"
              @click="confirmOrder(order, 'buyer')"
              :disabled="loading === order.id"
              class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition disabled:opacity-50 flex items-center gap-2"
            >
              <i
                v-if="loading === order.id"
                class="fa-regular fa-spinner fa-spin"
              ></i>
              <i v-else class="fa-regular fa-check"></i>
              Подтвердить получение
            </button>

            <button
              v-if="canCancel(order)"
              @click="showCancelModal(order)"
              class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
            >
              <i class="fa-regular fa-xmark"></i>
              Отменить сделку
            </button>

            <Link
              :href="
                route('chat.show', {
                  user: getOtherPartyId(order),
                  listing_type: getListingType(order.listable_type),
                  listing_id: order.listable_id,
                })
              "
              class="px-4 py-2 border-2 border-blue-200 rounded-lg hover:bg-blue-50 transition flex items-center gap-2 text-slate-600"
            >
              <i class="fa-regular fa-comment"></i>
              Чат
            </Link>

            <button
              v-if="canReview(order)"
              @click="openReviewModal(order)"
              class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center gap-2"
            >
              <i class="fa-regular fa-star"></i>
              Оставить отзыв
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Пустое состояние -->
    <div
      v-else
      class="bg-white rounded-lg shadow border-2 border-blue-100 p-12 text-center"
    >
      <i class="fa-regular fa-handshake text-6xl text-slate-300 mb-4"></i>
      <h3 class="text-xl font-semibold text-slate-800 mb-2">
        У вас пока нет сделок
      </h3>
      <p class="text-slate-500 mb-6">
        Перейдите в раздел объявлений, чтобы найти интересующие вас товары
      </p>
      <Link
        href="/"
        class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
      >
        <i class="fa-regular fa-search mr-2"></i>
        Перейти к объявлениям
      </Link>
    </div>

    <!-- Пагинация -->
    <div
      v-if="orders.links && orders.data && orders.data.length > 0"
      class="mt-6"
    >
      <Pagination :links="orders.links" />
    </div>
  </div>

  <!-- Модальное окно отмены сделки -->
  <div
    v-if="showCancelModalFor"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
  >
    <div
      class="fixed inset-0 bg-black bg-opacity-50"
      @click="showCancelModalFor = null"
    ></div>
    <div
      class="relative bg-white rounded-lg shadow-xl border-2 border-blue-100 max-w-md w-full p-6"
    >
      <h3 class="text-xl font-semibold mb-4 text-slate-800">Отменить сделку</h3>
      <p class="text-slate-600 mb-4">
        Вы уверены, что хотите отменить эту сделку? Это действие нельзя будет
        отменить.
      </p>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-700 mb-2">
          Причина отмены (необязательно)
        </label>
        <textarea
          v-model="cancelComment"
          rows="3"
          class="w-full px-3 py-2 border-2 border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white text-slate-800"
          placeholder="Укажите причину отмены..."
        ></textarea>
      </div>
      <div class="flex gap-3">
        <button
          @click="confirmCancel"
          :disabled="cancelling"
          class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition disabled:opacity-50 flex items-center justify-center gap-2"
        >
          <i v-if="cancelling" class="fa-regular fa-spinner fa-spin"></i>
          <span v-else>Подтвердить отмену</span>
        </button>
        <button
          @click="showCancelModalFor = null"
          class="flex-1 px-4 py-2 border-2 border-blue-200 rounded-lg hover:bg-blue-50 transition text-slate-600"
        >
          Отмена
        </button>
      </div>
    </div>
  </div>

  <!-- Модальное окно отзыва -->
  <ReviewModal
    v-model:show="showReviewModal"
    :seller-id="selectedSellerId"
    :current-user-id="currentUserId"
    :listing-type="selectedListingType"
    :listing-id="selectedListingId"
    @review-added="onReviewAdded"
  />
</template>

<script setup>
import MainLayout from "@/Layouts/Main.vue";
import { ref, reactive, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import ReviewModal from "@/Components/ReviewModal.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
  orders: {
    type: Object,
    required: true,
  },
  currentRole: {
    type: String,
    default: "all",
  },
  currentStatus: {
    type: String,
    default: "all",
  },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const currentUserId = computed(() => currentUser.value?.id);

const filters = reactive({
  role: props.currentRole,
  status: props.currentStatus,
});

const loading = ref(null);
const showReviewModal = ref(false);
const selectedSellerId = ref(null);
const selectedListingType = ref(null);
const selectedListingId = ref(null);
const showCancelModalFor = ref(null);
const cancelComment = ref("");
const cancelling = ref(false);

const stats = computed(() => {
  const allOrders = props.orders.data || [];
  return {
    total: allOrders.length,
    pending: allOrders.filter((o) => o.status === "pending").length,
    active: allOrders.filter((o) => o.status === "active").length,
    completed: allOrders.filter((o) => o.status === "completed").length,
    cancelled: allOrders.filter((o) => o.status === "cancelled").length,
  };
});

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const formatPrice = (price) => {
  if (!price && price !== 0) return "Цена не указана";
  return new Intl.NumberFormat("ru-RU").format(price) + " ₽";
};

const getStatusClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800",
    active: "bg-blue-100 text-blue-800",
    completed: "bg-green-100 text-green-800",
    cancelled: "bg-red-100 text-red-800",
    disputed: "bg-purple-100 text-purple-800",
  };
  return classes[status] || "bg-gray-100 text-gray-800";
};

const getStatusIcon = (status) => {
  const icons = {
    pending: "fa-clock",
    active: "fa-play",
    completed: "fa-check-circle",
    cancelled: "fa-times-circle",
    disputed: "fa-exclamation-circle",
  };
  return icons[status] || "fa-info-circle";
};

const getStatusText = (status) => {
  const texts = {
    pending: "Ожидает начала",
    active: "Активная",
    completed: "Завершена",
    cancelled: "Отменена",
    disputed: "Спор",
  };
  return texts[status] || status;
};

const getListingType = (modelType) => {
  if (!modelType) return null;
  const mapping = {
    "App\\Models\\AutoListing": "auto",
    "App\\Models\\NedvizhimostListing": "nedvizhimost",
    "App\\Models\\ElektronikaListing": "elektronika",
    "App\\Models\\HobbyListing": "hobby",
    "App\\Models\\RabotaVacancy": "vacancy",
    "App\\Models\\RabotaResume": "resume",
    "App\\Models\\UslugiListing": "uslugi",
    "App\\Models\\LichnieVeschiListing": "lichnie_veschi",
    "App\\Models\\DlyaDomaListing": "dlya_doma",
  };
  return mapping[modelType] || modelType;
};

const getOtherPartyId = (order) => {
  const userId = currentUserId.value;
  if (!userId) return null;
  return userId === order.buyer_id ? order.seller_id : order.buyer_id;
};

const canActivate = (order) => {
  const userId = currentUserId.value;
  if (!userId) return false;
  return order.status === "pending" && userId === order.seller_id;
};

const canConfirmSeller = (order) => {
  const userId = currentUserId.value;
  if (!userId) return false;
  return (
    order.status === "active" &&
    userId === order.seller_id &&
    !order.seller_confirmed_at
  );
};

const canConfirmBuyer = (order) => {
  const userId = currentUserId.value;
  if (!userId) return false;
  return (
    order.status === "active" &&
    userId === order.buyer_id &&
    order.seller_confirmed_at &&
    !order.buyer_confirmed_at
  );
};

const canCancel = (order) => {
  const userId = currentUserId.value;
  if (!userId) return false;
  return (
    ["pending", "active"].includes(order.status) &&
    (userId === order.buyer_id || userId === order.seller_id)
  );
};

const canReview = (order) => {
  const userId = currentUserId.value;
  if (!userId) return false;
  return order.status === "completed" && userId === order.buyer_id;
};

const getNextStepInfo = (order) => {
  const userId = currentUserId.value;
  if (!userId) return null;

  if (order.status === "pending") {
    if (userId === order.seller_id) {
      return {
        text: "Вы продавец. Нажмите «Начать сделку», когда будете готовы.",
        class: "text-blue-600",
      };
    } else if (userId === order.buyer_id) {
      return {
        text: "Ожидайте, пока продавец начнет сделку.",
        class: "text-slate-500",
      };
    }
  }

  if (order.status === "active") {
    if (userId === order.seller_id && !order.seller_confirmed_at) {
      return {
        text: "Нажмите «Подтвердить отправку» после отправки товара.",
        class: "text-blue-600",
      };
    } else if (
      userId === order.seller_id &&
      order.seller_confirmed_at &&
      !order.buyer_confirmed_at
    ) {
      return {
        text: "Ожидайте подтверждения получения от покупателя.",
        class: "text-slate-500",
      };
    } else if (userId === order.buyer_id && !order.seller_confirmed_at) {
      return {
        text: "Ожидайте подтверждения отправки от продавца.",
        class: "text-slate-500",
      };
    } else if (
      userId === order.buyer_id &&
      order.seller_confirmed_at &&
      !order.buyer_confirmed_at
    ) {
      return {
        text: "Продавец отправил товар. Нажмите «Подтвердить получение» после получения.",
        class: "text-green-600",
      };
    }
  }

  return null;
};

const applyFilters = () => {
  router.get(
    route("orders.index"),
    {
      role: filters.role,
      status: filters.status,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

const resetFilters = () => {
  filters.role = "all";
  filters.status = "all";
  applyFilters();
};

const activateOrder = async (order) => {
  loading.value = order.id;
  try {
    const response = await axios.post(`/api/v1/orders/${order.id}/activate`);
    alert(response.data.message || "Сделка успешно активирована");
    router.reload();
  } catch (error) {
    console.error("Ошибка при активации сделки:", error);
    alert(error.response?.data?.error || "Ошибка при активации сделки");
  } finally {
    loading.value = null;
  }
};

const confirmOrder = async (order, role) => {
  loading.value = order.id;
  const endpoint = role === "buyer" ? "confirm-buyer" : "confirm-seller";

  try {
    const { data } = await axios.post(`/api/v1/orders/${order.id}/${endpoint}`);
    if (data.completed) {
      alert("🎉 Сделка успешно завершена!");
    } else {
      alert(data.message || "Подтверждение выполнено");
    }
    router.reload();
  } catch (error) {
    console.error("Ошибка при подтверждении:", error);
    alert(error.response?.data?.error || "Ошибка при подтверждении");
  } finally {
    loading.value = null;
  }
};

const showCancelModal = (order) => {
  showCancelModalFor.value = order;
  cancelComment.value = "";
};

const confirmCancel = async () => {
  if (!showCancelModalFor.value) return;

  cancelling.value = true;
  try {
    await axios.post(`/api/v1/orders/${showCancelModalFor.value.id}/cancel`, {
      comment: cancelComment.value,
    });
    showCancelModalFor.value = null;
    cancelComment.value = "";
    router.reload();
  } catch (error) {
    console.error("Ошибка при отмене сделки:", error);
    alert(error.response?.data?.error || "Ошибка при отмене сделки");
  } finally {
    cancelling.value = false;
  }
};

const openReviewModal = (order) => {
  selectedSellerId.value = order.seller_id;
  selectedListingType.value = getListingType(order.listable_type);
  selectedListingId.value = order.listable_id;
  showReviewModal.value = true;
};

const onReviewAdded = () => {
  showReviewModal.value = false;
  router.reload();
};

const getListingImage = (order) => {
  if (!order.listable) return null;

  // Если есть main_image_url
  if (order.listable.main_image_url) {
    // Проверяем, начинается ли путь с /storage/
    if (order.listable.main_image_url.startsWith("/storage/")) {
      return order.listable.main_image_url;
    }
    // Если нет, добавляем /storage/
    return "/storage/" + order.listable.main_image_url;
  }

  // Если есть связь images
  if (order.listable.images && order.listable.images.length > 0) {
    const firstImage = order.listable.images[0];
    if (firstImage.url) {
      return firstImage.url;
    }
    if (firstImage.path) {
      return "/storage/" + firstImage.path;
    }
  }

  return null;
};
</script>