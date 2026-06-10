<template>
  <div class="max-w-7xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
      <!-- Заголовок чата -->
      <div class="border-b dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <Link
              :href="route('chat.index')"
              class="text-gray-500 hover:text-gray-700 dark:text-gray-400"
            >
              <i class="fa-regular fa-arrow-left text-xl"></i>
            </Link>

            <div class="flex items-center gap-3">
              <div class="relative">
                <div
                  class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center"
                >
                  <span class="text-white font-semibold">{{
                    recipient.name.charAt(0).toUpperCase()
                  }}</span>
                </div>
                <div
                  class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full"
                  :class="isOnline ? 'bg-green-500' : 'bg-gray-400'"
                ></div>
              </div>

              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <Link
                    :href="route('seller.profile', { user: recipient.id })"
                    class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 transition"
                  >
                    {{ recipient.name }}
                  </Link>

                  <!-- Тема чата (объявление) -->
                  <div v-if="listingInfo" class="flex items-center gap-2">
                    <span class="text-gray-400">·</span>
                    <Link
                      :href="
                        route('listing.show', {
                          type: listingType,
                          id: listingInfo.id,
                        })
                      "
                      class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 truncate max-w-[300px]"
                    >
                      {{ listingInfo.title }}
                    </Link>
                  </div>
                </div>

                <!-- Цена объявления и статус -->
                <div class="flex items-center gap-3 mt-1">
                  <p
                    v-if="listingInfo?.price"
                    class="text-sm text-gray-500 dark:text-gray-400"
                  >
                    {{ formatPrice(listingInfo.price) }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ isOnline ? "В сети" : "Был(а) недавно" }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <button
              @click="showListingInfo = !showListingInfo"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
              :class="{ 'bg-gray-100 dark:bg-gray-700': showListingInfo }"
            >
              <i class="fa-regular fa-info-circle text-gray-500"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="flex h-[calc(100vh-200px)]">
        <!-- Список сообщений -->
        <div class="flex-1 flex flex-col">
          <div
            ref="messagesContainer"
            class="flex-1 overflow-y-auto p-4 space-y-4"
          >
            <!-- Показываем тему чата если нет сообщений -->
            <div
              v-if="messages.length === 0 && listingInfo"
              class="text-center py-8"
            >
              <div
                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 inline-block"
              >
                <p class="text-gray-600 dark:text-gray-400 mb-2">
                  Начало переписки по объявлению:
                </p>
                <Link
                  :href="
                    route('listing.show', {
                      type: listingType,
                      id: listingInfo.id,
                    })
                  "
                  class="text-blue-600 dark:text-blue-400 font-medium hover:underline"
                >
                  {{ listingInfo.title }}
                </Link>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                  {{ formatPrice(listingInfo.price) }}
                </p>
              </div>
            </div>

            <div
              v-for="message in messages"
              :key="message.id"
              class="flex"
              :class="
                message.sender_id === authUser.id
                  ? 'justify-end'
                  : 'justify-start'
              "
            >
              <div class="max-w-[70%]">
                <div
                  class="rounded-lg p-3"
                  :class="
                    message.sender_id === authUser.id
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                  "
                >
                  <p class="whitespace-pre-wrap break-words">
                    {{ message.text }}
                    <span v-if="message.pending" class="ml-1 text-xs">⏳</span>
                    <span v-if="message.error" class="ml-1 text-xs text-red-500"
                      >❌</span
                    >
                  </p>
                </div>
                <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                  <span>{{ formatTime(message.created_at) }}</span>
                  <span
                    v-if="message.sender_id === authUser.id && !message.pending"
                  >
                    <i
                      v-if="message.read_at"
                      class="fa-regular fa-check-double text-green-500"
                    ></i>
                    <i v-else class="fa-regular fa-check"></i>
                  </span>
                </div>
              </div>
            </div>

            <div v-if="isTyping" class="flex justify-start">
              <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3">
                <div class="flex gap-1">
                  <span
                    class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"
                  ></span>
                  <span
                    class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"
                    style="animation-delay: 0.1s"
                  ></span>
                  <span
                    class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"
                    style="animation-delay: 0.2s"
                  ></span>
                </div>
              </div>
            </div>
          </div>

          <div class="border-t dark:border-gray-700 p-4">
            <form @submit.prevent="sendMessage" class="flex gap-2">
              <input
                type="text"
                v-model="newMessage"
                @keydown="handleTyping"
                :placeholder="
                  listingInfo
                    ? `Написать по объявлению...`
                    : 'Введите сообщение...'
                "
                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
              <button
                type="submit"
                :disabled="!newMessage.trim()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
              >
                <i class="fa-regular fa-paper-plane"></i>
              </button>
            </form>
          </div>
        </div>

        <!-- Информация об объявлении (сайдбар) -->
        <div
          v-if="showListingInfo && listingInfo"
          class="w-80 border-l dark:border-gray-700 p-4 overflow-y-auto"
        >
          <h3 class="font-semibold text-lg mb-3 text-gray-900 dark:text-white">
            Информация о сделке
          </h3>

          <div class="mb-4">
            <Link
              :href="
                route('listing.show', {
                  type: listingType,
                  id: listingInfo.id,
                })
              "
              class="block"
            >
              <img
                v-if="listingInfo.main_image_url"
                :src="listingInfo.main_image_url"
                class="w-full h-32 object-cover rounded-lg mb-2"
              />
              <div
                v-else
                class="w-full h-32 bg-gray-200 dark:bg-gray-700 rounded-lg mb-2 flex items-center justify-center"
              >
                <i class="fa-regular fa-image text-gray-400 text-3xl"></i>
              </div>
              <h4 class="font-medium text-gray-900 dark:text-white">
                {{ listingInfo.title }}
              </h4>
              <p class="text-blue-600 dark:text-blue-400 font-bold">
                {{ formatPrice(listingInfo.price) }}
              </p>
            </Link>
          </div>

          <!-- Компонент сделки -->
          <DealActions
            v-if="listingInfo"
            :seller-id="recipient.id"
            :buyer-id="authUser.id"
            :listing-type="listingInfo.listing_type"
            :listing-id="listingInfo.id"
            :listing-title="listingInfo.title"
            :amount="Number(listingInfo.price)"
            @deal-updated="refreshDealStatus"
          />

          <div v-if="canReview" class="mt-4">
            <button
              @click="openReviewModal"
              class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition"
            >
              <i class="fa-regular fa-star mr-2"></i>
              Оставить отзыв
            </button>
          </div>
        </div>

        <!-- Заглушка если нет информации об объявлении -->
        <div
          v-else-if="showListingInfo"
          class="w-80 border-l dark:border-gray-700 p-4 overflow-y-auto"
        >
          <h3 class="font-semibold text-lg mb-3 text-gray-900 dark:text-white">
            Информация
          </h3>
          <p class="text-gray-500 dark:text-gray-400 text-sm">
            Чат не привязан к конкретному объявлению
          </p>
        </div>
      </div>
    </div>
  </div>

  <ReviewModal
    v-model:show="showReviewModal"
    :seller-id="recipient.id"
    :current-user-id="authUser.id"
    :listing-type="listingInfo?.listing_type"
    :listing-id="listingInfo?.id"
    @review-added="onReviewAdded"
  />
</template>

<script setup>
import MainLayout from "@/Layouts/Main.vue";
import { ref, onMounted, onUnmounted, nextTick, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import DealActions from "@/Components/DealActions.vue";
import ReviewModal from "@/Components/ReviewModal.vue";

const props = defineProps({
  recipient: Object,
  messages: Array,
  authUser: Object,
});

const newMessage = ref("");
const messagesContainer = ref(null);
const isTyping = ref(false);
let typingTimeout = null;
let channel = null;
let typingChannel = null;
const isOnline = ref(false);
const showListingInfo = ref(true);
const listingInfo = ref(null);
const listingId = ref(null);
const listingType = ref(null);
const canReview = ref(false);
const showReviewModal = ref(false);

// Добавляем реактивный список сообщений
const messages = ref([]);
// Set для отслеживания ID временных сообщений
const pendingMessageIds = new Set();

// Форматирование имени модели
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

const formatTime = (date) => {
  const d = new Date(date);
  const now = new Date();
  const diff = now - d;
  if (diff < 86400000) {
    return d.toLocaleTimeString("ru-RU", {
      hour: "2-digit",
      minute: "2-digit",
    });
  } else {
    return d.toLocaleDateString("ru-RU", { day: "numeric", month: "short" });
  }
};

const formatPrice = (price) => {
  if (!price && price !== 0) return "Цена не указана";
  return new Intl.NumberFormat("ru-RU").format(price) + " ₽";
};

// Загрузка информации об объявлении из URL
const loadListingFromUrl = () => {
  const urlParams = new URLSearchParams(window.location.search);
  listingId.value = urlParams.get("listing_id");
  listingType.value = urlParams.get("listing_type");

  console.log("📦 Параметры из URL:", {
    listingId: listingId.value,
    listingType: listingType.value,
    fullUrl: window.location.href,
  });

  if (listingId.value && listingType.value) {
    fetchListingInfo();
  }
};

// Получение информации об объявлении
const fetchListingInfo = async () => {
  if (!listingId.value || !listingType.value) return;

  try {
    const { data } = await axios.get(
      `/api/v1/listings/${listingType.value}/${listingId.value}`
    );
    listingInfo.value = data;
    console.log("✅ Информация об объявлении загружена:", data);
    checkCanReview();
  } catch (error) {
    console.error("❌ Error fetching listing:", error);
  }
};

const checkCanReview = async () => {
  if (!listingInfo.value) return;
  try {
    const { data } = await axios.get(
      `/api/v1/sellers/${props.recipient.id}/reviews`
    );
    canReview.value = data.can_review;
  } catch (error) {
    console.error("Error checking review:", error);
  }
};

const refreshDealStatus = () => {
  checkCanReview();
};

const openReviewModal = () => {
  showReviewModal.value = true;
};

const onReviewAdded = () => {
  canReview.value = false;
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

const sendMessage = async () => {
  if (!newMessage.value.trim()) return;

  const text = newMessage.value;
  const tempId =
    "temp_" + Date.now() + "_" + Math.random().toString(36).substr(2, 9);

  // Оптимистично добавляем сообщение
  const tempMessage = {
    id: tempId,
    text: text,
    sender_id: props.authUser.id,
    receiver_id: props.recipient.id,
    created_at: new Date().toISOString(),
    pending: true,
  };

  messages.value.push(tempMessage);
  pendingMessageIds.add(tempId);
  newMessage.value = "";
  scrollToBottom();

  try {
    const payload = {
      text: text,
    };

    // Добавляем информацию об объявлении если есть
    if (listingType.value && listingId.value) {
      payload.listing_type = listingType.value;
      payload.listing_id = listingId.value;

      if (listingInfo.value) {
        payload.subject = listingInfo.value.title;
      }
    }

    const { data } = await axios.post(
      `/chat/send/${props.recipient.id}`,
      payload
    );

    // Заменяем временное сообщение на реальное
    const index = messages.value.findIndex((m) => m.id === tempId);
    if (index !== -1) {
      messages.value[index] = { ...data, pending: false };
      pendingMessageIds.delete(tempId);
    }

    scrollToBottom();
  } catch (error) {
    console.error("Error sending message:", error);
    const index = messages.value.findIndex((m) => m.id === tempId);
    if (index !== -1) {
      messages.value[index].error = true;
      messages.value[index].pending = false;
      pendingMessageIds.delete(tempId);
    }
  }
};

let typingThrottle = null;

const handleTyping = () => {
  if (!props.recipient?.id || !window.Echo) return;

  if (typingThrottle) return;

  window.Echo.private(`chat.${props.recipient.id}`).whisper("typing", {
    userId: props.authUser.id,
  });

  typingThrottle = setTimeout(() => {
    typingThrottle = null;
  }, 800);
};

const subscribeToChannels = () => {
  if (!window.Echo) {
    console.error("❌ Echo not initialized");
    return;
  }

  console.log("📡 Subscribing to channel for user:", props.authUser.id);

  // Отписываемся от предыдущих каналов
  if (channel) {
    window.Echo.leave(`chat.${props.authUser.id}`);
  }
  if (typingChannel) {
    window.Echo.leave(`chat.${props.authUser.id}`);
  }

  // Подписываемся на канал текущего пользователя
  channel = window.Echo.private(`chat.${props.authUser.id}`)
    .listen(".MessageSent", (e) => {
      console.log("📨 Received message via WebSocket:", e);

      const message = e.message;

      // ПРОВЕРЯЕМ, НЕ ЯВЛЯЕТСЯ ЛИ ЭТО СООБЩЕНИЕ ТЕМ, ЧТО МЫ ТОЛЬКО ЧТО ОТПРАВИЛИ
      // Если это наше сообщение, которое мы отправили, и у нас есть его временная версия - игнорируем
      if (message.sender_id === props.authUser.id) {
        // Проверяем, есть ли у нас уже это сообщение (реальное, не временное)
        const existingRealMessage = messages.value.find(
          (m) => m.id === message.id
        );
        if (existingRealMessage) {
          console.log(
            "⚠️ Real message already exists, ignoring WebSocket event"
          );
          return;
        }

        // Проверяем, есть ли временное сообщение с таким же текстом и недавним временем
        const recentTempMessage = messages.value.find(
          (m) =>
            m.pending &&
            m.sender_id === props.authUser.id &&
            m.text === message.text &&
            Math.abs(new Date(m.created_at) - new Date()) < 5000 // в пределах 5 секунд
        );

        if (recentTempMessage) {
          console.log(
            "⚠️ Found pending message with same text, ignoring WebSocket event"
          );
          return;
        }
      }

      // Проверяем, что сообщение относится к текущему диалогу
      const isRelevantSender =
        message.sender_id === props.recipient.id ||
        (message.sender_id === props.authUser.id &&
          message.receiver_id === props.recipient.id);

      // Проверяем соответствие объявления
      const messageListingId =
        message.listable?.id || message.listable?.listable_id;
      const isSameListing =
        (!listingId.value && !messageListingId) ||
        (message.listable?.listing_type === listingType.value &&
          messageListingId == listingId.value);

      console.log("📨 Checking message match:", {
        isRelevantSender,
        isSameListing,
        messageSenderId: message.sender_id,
        messageReceiverId: message.receiver_id,
        recipientId: props.recipient.id,
        currentUserId: props.authUser.id,
        messageListingId,
        currentListingId: listingId.value,
      });

      // Если сообщение относится к текущему диалогу
      if (isRelevantSender && isSameListing) {
        // Проверяем, нет ли уже такого сообщения
        const exists = messages.value.some((m) => m.id === message.id);
        if (!exists) {
          console.log("✅ Adding new message to chat");
          messages.value.push(message);
          scrollToBottom();
          markMessagesAsRead();
        } else {
          console.log("⚠️ Message already exists");
        }
      }
    })
    .subscribed(() => {
      console.log("✅ Successfully subscribed to channel");
    })
    .error((error) => {
      console.error("❌ Channel subscription error:", error);
    });

  // Подписываемся на события печатания
  typingChannel = window.Echo.private(
    `chat.${props.authUser.id}`
  ).listenForWhisper("typing", (e) => {
    console.log("⌨️ Typing event received:", e);
    if (e.userId === props.recipient.id) {
      isTyping.value = true;

      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(() => {
        isTyping.value = false;
      }, 800);
    }
  });
};

const checkOnlineStatus = async () => {
  if (!props.recipient?.id) return;

  try {
    const { data } = await axios.get(
      `/chat/online-status/${props.recipient.id}`
    );
    isOnline.value = data.online || false;
  } catch (error) {
    console.error("Error checking online status:", error);
    // При ошибке считаем пользователя офлайн
    isOnline.value = false;
  }
};

// Добавим функцию для отправки пинга о том, что пользователь онлайн
const sendOnlinePing = async () => {
  try {
    await axios.post("/chat/update-online-status");
  } catch (error) {
    console.error("Error sending online ping:", error);
  }
};

const markMessagesAsRead = async () => {
  try {
    const payload = {};
    if (listingType.value && listingId.value) {
      payload.listing_type = listingType.value;
      payload.listing_id = listingId.value;
    }

    await axios.post(`/chat/mark-as-read/${props.recipient.id}`, payload);
  } catch (error) {
    console.error("Error marking messages as read:", error);
  }
};

onMounted(() => {
  // Инициализируем сообщения из props
  messages.value = (props.messages || []).map((m) => ({
    ...m,
    pending: false,
  }));

  scrollToBottom();
  subscribeToChannels();
  checkOnlineStatus();
  markMessagesAsRead();
  loadListingFromUrl();

  // Отправляем пинг, что пользователь онлайн
  sendOnlinePing();

  // Проверяем статус каждые 30 секунд
  const onlineInterval = setInterval(checkOnlineStatus, 30000);

  // Отправляем пинг каждые 3 минуты
  const pingInterval = setInterval(sendOnlinePing, 180000);

  // Очищаем интервалы при размонтировании
  onUnmounted(() => {
    clearInterval(onlineInterval);
    clearInterval(pingInterval);

    if (channel) {
      window.Echo.leave(`chat.${props.authUser.id}`);
    }
    if (typingChannel) {
      window.Echo.leave(`chat.${props.authUser.id}`);
    }
    if (typingTimeout) {
      clearTimeout(typingTimeout);
    }
  });
});

// Следим за изменением props.messages
watch(
  () => props.messages,
  (newMessages) => {
    if (newMessages) {
      // Сохраняем только те сообщения, которых еще нет в списке
      const existingIds = new Set(messages.value.map((m) => m.id));
      const newUniqueMessages = (newMessages || []).filter(
        (m) => !existingIds.has(m.id)
      );

      if (newUniqueMessages.length > 0) {
        messages.value = [
          ...messages.value,
          ...newUniqueMessages.map((m) => ({ ...m, pending: false })),
        ];
        scrollToBottom();
      }
    }
  },
  { immediate: true, deep: true }
);

// Следим за изменением messages для скролла
watch(
  messages,
  () => {
    scrollToBottom();
  },
  { deep: true }
);
</script>

<style scoped>
@keyframes bounce {
  0%,
  80%,
  100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

.animate-bounce {
  animation: bounce 1.4s infinite ease-in-out both;
}
</style>