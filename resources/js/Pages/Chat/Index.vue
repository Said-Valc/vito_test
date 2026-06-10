<script setup>
import MainLayout from "../../Layouts/Main.vue";
import { ref, onMounted } from "vue";
import { router, Link } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  users: Array,
  authUser: Object,
  conversations: Array,
});

const conversations = ref(props.conversations || []);
const isLoading = ref(false);

// Получаем список диалогов
const fetchConversations = async () => {
  isLoading.value = true;
  try {
    const { data } = await axios.get("/chat/conversations");
    conversations.value = data;
    console.log("Conversations loaded:", data);
  } catch (error) {
    console.error("Error fetching conversations:", error);
  } finally {
    isLoading.value = false;
  }
};

// Форматирование даты
const formatDate = (date) => {
  if (!date) return "";
  const messageDate = new Date(date);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  if (messageDate.toDateString() === today.toDateString()) {
    return messageDate.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    });
  } else if (messageDate.toDateString() === yesterday.toDateString()) {
    return "Вчера";
  } else {
    return messageDate.toLocaleDateString();
  }
};

// Получить тип объявления из полиморфной связи
const getListingType = (listable) => {
  if (!listable) return null;

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

  return mapping[listable.listable_type] || listable.listing_type || null;
};

// Открыть чат с учетом объявления
const openChat = (conv) => {
  let url = `/chats/${conv.user.id}`;

  // Добавляем параметры объявления если есть
  if (conv.listable) {
    const type = getListingType(conv.listable);
    const id = conv.listable.id || conv.listable.listable_id;

    if (type && id) {
      url += `?listing_type=${type}&listing_id=${id}`;
    }
  }

  router.visit(url);
};

// Уникальный ключ для диалога
const getConversationKey = (conv) => {
  const listingId =
    conv.listable?.id || conv.listable?.listable_id || "general";
  return `${conv.user.id}_${listingId}`;
};

onMounted(() => {
  if (!conversations.value.length) {
    fetchConversations();
  }
});
</script>

<template>
  <MainLayout>
    <div class="max-w-3xl mx-auto">
      <h1 class="text-2xl font-bold mb-6">Сообщения</h1>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <!-- Список диалогов -->
        <div v-if="!isLoading && conversations.length > 0">
          <div
            v-for="conv in conversations"
            :key="getConversationKey(conv)"
            @click="openChat(conv)"
            class="p-4 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
          >
            <div class="flex items-start gap-3">
              <!-- Аватар -->
              <div class="flex-shrink-0">
                <div
                  class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center"
                >
                  <span class="text-white font-semibold">
                    {{ conv.user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <p class="font-semibold text-gray-900 dark:text-white">
                    {{ conv.user.name }}
                  </p>
                  <span class="text-xs text-gray-400">
                    {{ formatDate(conv.last_message?.created_at) }}
                  </span>
                </div>

                <!-- Тема (объявление) -->
                <p
                  v-if="conv.subject"
                  class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1 truncate"
                >
                  {{ conv.subject }}
                </p>
                <p
                  v-else-if="conv.listable?.title"
                  class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1 truncate"
                >
                  {{ conv.listable.title }}
                </p>

                <!-- Последнее сообщение -->
                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                  <span
                    v-if="conv.last_message?.sender_id === authUser?.id"
                    class="text-gray-400 dark:text-gray-500"
                  >
                    Вы:
                  </span>
                  {{ conv.last_message?.text || "Нет сообщений" }}
                </p>
              </div>

              <!-- Счетчик непрочитанных -->
              <div v-if="conv.unread_count > 0" class="ml-2">
                <span
                  class="bg-red-500 text-white text-xs font-medium rounded-full w-6 h-6 flex items-center justify-center"
                >
                  {{ conv.unread_count > 9 ? "9+" : conv.unread_count }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Состояние загрузки -->
        <div v-if="isLoading" class="p-8 text-center">
          <div class="text-gray-500 dark:text-gray-400">
            Загрузка сообщений...
          </div>
        </div>

        <!-- Нет диалогов -->
        <div
          v-if="!isLoading && conversations.length === 0"
          class="p-8 text-center"
        >
          <div class="text-gray-500 dark:text-gray-400 mb-4">
            У вас пока нет диалогов
          </div>
          <Link
            :href="route('home')"
            class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition"
          >
            Найти объявления
          </Link>
        </div>

        <!-- Список всех пользователей для начала чата -->
        <div
          v-if="users && users.length > 0"
          class="border-t dark:border-gray-700"
        >
          <div class="p-4 bg-gray-50 dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
              Начать новый чат
            </h3>
          </div>
          <div
            v-for="user in users"
            :key="user.id"
            @click="openChat({ user })"
            class="p-4 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">
                  {{ user.name }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ user.email }}
                </p>
              </div>
              <button
                class="text-blue-500 hover:text-blue-600 dark:text-blue-400"
              >
                <i class="fa-regular fa-comment mr-1"></i>
                Написать
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>