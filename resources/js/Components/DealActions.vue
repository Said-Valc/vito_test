<template>
  <div class="deal-actions">
    <!-- Информация о существующей сделке -->
    <div v-if="order" class="space-y-3">
      <div
        class="text-sm text-center py-2 px-3 rounded-lg font-medium"
        :class="{
          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300':
            order.status === 'pending',
          'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300':
            order.status === 'active',
          'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300':
            order.status === 'completed',
          'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300':
            order.status === 'cancelled',
        }"
      >
        <i class="fa-regular mr-1" :class="getStatusIcon(order.status)"></i>
        {{ getStatusText(order.status) }}
      </div>

      <!-- Информация о следующем шаге -->
      <div
        v-if="getStatusMessage()"
        class="text-sm p-3 rounded-lg"
        :class="getStatusMessage().class"
      >
        <i class="fa-regular mr-1" :class="getStatusMessage().icon"></i>
        {{ getStatusMessage().text }}
      </div>

      <!-- Прогресс сделки -->
      <div v-if="order.status === 'active'" class="space-y-2">
        <div class="flex justify-between text-xs">
          <span
            :class="{
              'text-green-600 dark:text-green-400': order.seller_confirmed_at,
            }"
          >
            <i
              class="fa-regular mr-1"
              :class="
                order.seller_confirmed_at ? 'fa-check-circle' : 'fa-circle'
              "
            ></i>
            Продавец отправил
          </span>
          <span
            :class="{
              'text-green-600 dark:text-green-400': order.buyer_confirmed_at,
            }"
          >
            <i
              class="fa-regular mr-1"
              :class="
                order.buyer_confirmed_at ? 'fa-check-circle' : 'fa-circle'
              "
            ></i>
            Покупатель получил
          </span>
        </div>
        <div
          class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden"
        >
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

      <!-- Ссылка на все сделки -->
      <Link
        :href="route('orders.index')"
        class="block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm"
      >
        <i class="fa-regular fa-list mr-2"></i>
        Все сделки
      </Link>

      <!-- Кнопки действий -->
      <div v-if="showActionButton" class="pt-2">
        <button
          @click="handleAction"
          :disabled="loading"
          class="w-full px-4 py-2 rounded-lg transition flex items-center justify-center gap-2 disabled:opacity-50"
          :class="showActionButton.class"
        >
          <i v-if="loading" class="fa-regular fa-spinner fa-spin"></i>
          <i v-else :class="showActionButton.icon"></i>
          {{ showActionButton.text }}
        </button>
      </div>
    </div>

    <!-- Кнопка создания сделки (только для покупателя) -->
    <button
      v-else-if="isAuthenticated && isBuyer"
      @click="showCreateModal = true"
      :disabled="loading"
      class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50"
    >
      <i v-if="loading" class="fa-regular fa-spinner fa-spin mr-2"></i>
      <i v-else class="fa-regular fa-handshake mr-2"></i>
      Предложить сделку
    </button>

    <!-- Не авторизован -->
    <div
      v-else-if="!isAuthenticated"
      class="text-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg"
    >
      <p class="text-sm text-gray-600 dark:text-gray-400">
        <a :href="route('login')" class="text-blue-600 hover:underline"
          >Войдите</a
        >, чтобы предложить сделку
      </p>
    </div>

    <!-- Продавец видит свои объявления (не может предложить сделку сам себе) -->
    <div
      v-else-if="isSeller"
      class="text-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg"
    >
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Это ваше объявление
      </p>
    </div>

    <!-- Модальное окно создания сделки -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div
        class="fixed inset-0 bg-black bg-opacity-50"
        @click="showCreateModal = false"
      ></div>
      <div
        class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6"
      >
        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
          Предложить сделку
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Вы хотите приобрести "{{ listingTitle }}"
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          После создания сделки продавец получит уведомление и сможет её начать.
        </p>
        <div class="flex gap-3">
          <button
            @click="createOrder"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50"
          >
            <i v-if="loading" class="fa-regular fa-spinner fa-spin mr-2"></i>
            Подтвердить
          </button>
          <button
            @click="showCreateModal = false"
            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
          >
            Отмена
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  sellerId: { type: Number, required: true },
  buyerId: { type: Number, required: true },
  listingType: { type: String, required: true },
  listingId: { type: Number, required: true },
  listingTitle: { type: String, required: true },
  amount: { type: Number, default: null },
});

const emit = defineEmits(["deal-updated"]);

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const order = ref(null);
const loading = ref(false);
const showCreateModal = ref(false);
const isAuthenticated = computed(() => !!currentUser.value);
const isBuyer = computed(() => currentUser.value?.id === props.buyerId);
const isSeller = computed(() => currentUser.value?.id === props.sellerId);

let echoChannel = null;

const formatModelName = (type) => {
  const mapping = {
    auto: "AutoListing",
    nedvizhimost: "NedvizhimostListing",
    elektronika: "ElektronikaListing",
    hobby: "HobbyListing",
    vacancy: "RabotaVacancy",
    resume: "RabotaResume",
    uslugi: "UslugiListing",
    lichnie_veschi: "LichnieVeschiListing",
    dlya_doma: "DlyaDomaListing",
  };
  return mapping[type] || type;
};

const getStatusIcon = (status) => {
  const icons = {
    pending: "fa-clock",
    active: "fa-handshake",
    completed: "fa-circle-check",
    cancelled: "fa-circle-xmark",
  };
  return icons[status] || "fa-info-circle";
};

const getStatusText = (status) => {
  const texts = {
    pending: "Ожидает начала сделки",
    active: "Сделка в процессе",
    completed: "Сделка завершена",
    cancelled: "Сделка отменена",
  };
  return texts[status] || status;
};

// Получить сообщение о текущем статусе
const getStatusMessage = () => {
  if (!order.value) return null;

  const status = order.value.status;

  if (status === "pending") {
    if (isSeller.value) {
      return {
        icon: "fa-info-circle",
        text: "Покупатель предложил сделку. Нажмите «Начать сделку», когда будете готовы.",
        class:
          "bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300",
      };
    } else if (isBuyer.value) {
      return {
        icon: "fa-clock",
        text: "Ожидайте, пока продавец начнет сделку.",
        class:
          "bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300",
      };
    }
  }

  if (status === "active") {
    if (isSeller.value && !order.value.seller_confirmed_at) {
      return {
        icon: "fa-paper-plane",
        text: "Сделка активна. Подтвердите отправку товара.",
        class:
          "bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300",
      };
    } else if (
      isSeller.value &&
      order.value.seller_confirmed_at &&
      !order.value.buyer_confirmed_at
    ) {
      return {
        icon: "fa-clock",
        text: "Вы подтвердили отправку. Ожидайте подтверждения получения от покупателя.",
        class:
          "bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300",
      };
    } else if (isBuyer.value && !order.value.seller_confirmed_at) {
      return {
        icon: "fa-clock",
        text: "Ожидайте подтверждения отправки от продавца.",
        class:
          "bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300",
      };
    } else if (
      isBuyer.value &&
      order.value.seller_confirmed_at &&
      !order.value.buyer_confirmed_at
    ) {
      return {
        icon: "fa-check-circle",
        text: "Продавец отправил товар. Подтвердите получение.",
        class:
          "bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300",
      };
    } else if (isBuyer.value && order.value.buyer_confirmed_at) {
      return {
        icon: "fa-check-circle",
        text: "Вы подтвердили получение. Ожидайте завершения сделки.",
        class:
          "bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300",
      };
    }
  }

  if (status === "completed") {
    return {
      icon: "fa-trophy",
      text: "Сделка успешно завершена!",
      class:
        "bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300",
    };
  }

  if (status === "cancelled") {
    return {
      icon: "fa-ban",
      text: "Сделка была отменена.",
      class: "bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300",
    };
  }

  return null;
};

// Кнопка действия
const showActionButton = computed(() => {
  if (!order.value || !currentUser.value) return null;

  const status = order.value.status;

  // Начать сделку - ТОЛЬКО для продавца
  if (status === "pending" && isSeller.value) {
    return {
      text: "Начать сделку",
      icon: "fa-regular fa-play",
      action: "activate",
      class: "bg-green-600 text-white hover:bg-green-700",
    };
  }

  // Подтвердить отправку - ТОЛЬКО для продавца
  if (
    status === "active" &&
    isSeller.value &&
    !order.value.seller_confirmed_at
  ) {
    return {
      text: "Подтвердить отправку",
      icon: "fa-regular fa-paper-plane",
      action: "confirmSeller",
      class: "bg-blue-600 text-white hover:bg-blue-700",
    };
  }

  // Подтвердить получение - ТОЛЬКО для покупателя и только после отправки продавцом
  if (
    status === "active" &&
    isBuyer.value &&
    order.value.seller_confirmed_at &&
    !order.value.buyer_confirmed_at
  ) {
    return {
      text: "Подтвердить получение",
      icon: "fa-regular fa-check",
      action: "confirmBuyer",
      class: "bg-blue-600 text-white hover:bg-blue-700",
    };
  }

  // Отменить сделку - для обоих в статусах pending и active
  if (
    (status === "pending" || status === "active") &&
    (isBuyer.value || isSeller.value)
  ) {
    return {
      text: "Отменить сделку",
      icon: "fa-regular fa-xmark",
      action: "cancel",
      class: "bg-red-600 text-white hover:bg-red-700",
    };
  }

  return null;
});

const handleAction = () => {
  if (!showActionButton.value) return;

  switch (showActionButton.value.action) {
    case "activate":
      activateOrder();
      break;
    case "confirmSeller":
      confirmAsSeller();
      break;
    case "confirmBuyer":
      confirmAsBuyer();
      break;
    case "cancel":
      cancelOrder();
      break;
  }
};

const fetchOrder = async () => {
  if (!props.listingType || !props.listingId) return;
  try {
    const listableType = `App\\Models\\${formatModelName(props.listingType)}`;
    const { data } = await axios.get("/api/v1/orders/check", {
      params: { listable_type: listableType, listable_id: props.listingId },
    });
    order.value = data.order;
  } catch (error) {
    console.error("Error fetching order:", error);
  }
};

const createOrder = async () => {
  loading.value = true;
  try {
    const listableType = `App\\Models\\${formatModelName(props.listingType)}`;
    const { data } = await axios.post("/api/v1/orders", {
      seller_id: props.sellerId,
      listable_type: listableType,
      listable_id: props.listingId,
      amount: props.amount ? Number(props.amount) : null,
    });
    order.value = data;
    showCreateModal.value = false;
    emit("deal-updated");
    alert("Сделка создана! Продавец получит уведомление.");
  } catch (error) {
    console.error("Error creating order:", error);
    alert(error.response?.data?.error || "Ошибка при создании сделки");
  } finally {
    loading.value = false;
  }
};

const activateOrder = async () => {
  loading.value = true;
  try {
    const { data } = await axios.post(
      `/api/v1/orders/${order.value.id}/activate`
    );
    order.value = data.order;
    emit("deal-updated");
    alert(
      data.message ||
        "Сделка активирована. Теперь вы можете подтвердить отправку."
    );
  } catch (error) {
    console.error("Error activating order:", error);
    alert(error.response?.data?.error || "Ошибка при активации сделки");
  } finally {
    loading.value = false;
  }
};

const confirmAsBuyer = async () => {
  loading.value = true;
  try {
    const { data } = await axios.post(
      `/api/v1/orders/${order.value.id}/confirm-buyer`
    );
    order.value = data.order;
    if (data.completed) {
      alert("🎉 Сделка успешно завершена! Теперь вы можете оставить отзыв.");
    } else {
      alert("Получение подтверждено.");
    }
    emit("deal-updated");
  } catch (error) {
    console.error("Error confirming:", error);
    alert(error.response?.data?.error || "Ошибка при подтверждении получения");
  } finally {
    loading.value = false;
  }
};

const confirmAsSeller = async () => {
  loading.value = true;
  try {
    const { data } = await axios.post(
      `/api/v1/orders/${order.value.id}/confirm-seller`
    );
    order.value = data.order;
    alert(
      data.message ||
        "Отправка подтверждена. Ожидайте подтверждения от покупателя."
    );
    emit("deal-updated");
  } catch (error) {
    console.error("Error confirming:", error);
    alert(error.response?.data?.error || "Ошибка при подтверждении отправки");
  } finally {
    loading.value = false;
  }
};

const cancelOrder = async () => {
  if (!confirm("Вы уверены, что хотите отменить эту сделку?")) return;
  loading.value = true;
  try {
    const { data } = await axios.post(
      `/api/v1/orders/${order.value.id}/cancel`
    );
    order.value = data.order;
    emit("deal-updated");
    alert("Сделка отменена.");
  } catch (error) {
    console.error("Error cancelling:", error);
    alert(error.response?.data?.error || "Ошибка при отмене сделки");
  } finally {
    loading.value = false;
  }
};

// Подписка на обновления сделки через WebSocket
const subscribeToOrderUpdates = () => {
  if (!window.Echo || !currentUser.value) return;

  const userId = currentUser.value.id;

  // Подписываемся на канал пользователя для получения уведомлений
  echoChannel = window.Echo.private(`App.Models.User.${userId}`).notification(
    (notification) => {
      console.log("Received notification:", notification);

      // Если уведомление связано со сделкой, обновляем статус
      if (
        notification.type === "order_status_changed" ||
        notification.type === "new_order"
      ) {
        fetchOrder();
      }
    }
  );
};

onMounted(() => {
  fetchOrder();
  subscribeToOrderUpdates();
});

onUnmounted(() => {
  if (echoChannel) {
    window.Echo.leave(`App.Models.User.${currentUser.value?.id}`);
  }
});

// Экспортируем функцию для обновления извне
defineExpose({
  refreshOrder: fetchOrder,
});
</script>