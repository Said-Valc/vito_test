<script setup>
import { switchTheme } from "../theme";
import NavLink from "../Components/NavLink.vue";
import CategoriesMenu from "../Components/CategoriesMenu.vue";
import Header from "../Components/Header.vue";
import { usePage, Link } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import ChatModal from "@/Components/ChatModal.vue";
import { useGeolocation } from "@/composables/useGeolocation";

const page = usePage();
const user = computed(() => page.props.auth.user);

const show = ref(false);
const showChatModal = ref(false);
const conversations = ref([]);
const unreadCount = ref(0);

let channel = null;
const {
  init: initGeolocation,
  userLocation,
  refreshLocation,
} = useGeolocation();

// Получаем список диалогов (нужно для счетчика и для модалки)
const fetchConversations = async () => {
  if (!user.value) return;

  try {
    const { data } = await axios.get("/chat/conversations");
    conversations.value = data;
    updateUnreadCount();
  } catch (e) {
    console.error("Ошибка при получении диалогов:", e);
  }
};

// Считаем общее кол-во непрочитанных
const updateUnreadCount = () => {
  unreadCount.value = conversations.value.reduce(
    (sum, conv) => sum + (conv.unread_count || 0),
    0
  );
};

const openChatModal = () => {
  showChatModal.value = true;
  fetchConversations();
};

const closeChatModal = () => {
  showChatModal.value = false;
  fetchConversations();
};

// Обновление диалогов из модалки
const handleRefresh = () => {
  fetchConversations();
};

// Глобальная подписка на уведомления
const subscribeGlobal = () => {
  if (!window.Echo || !user.value) return;

  // Слушаем приватный канал пользователя
  channel = window.Echo.private(`chat.${user.value.id}`).listen(
    "MessageSent",
    (e) => {
      // Обновляем данные в фоне
      fetchConversations();

      // Логика показа браузерного уведомления
      const isOnTargetChatPage = window.location.pathname.includes(
        `/chats/${e.message.sender_id}`
      );

      if (!isOnTargetChatPage && !showChatModal.value) {
        if (Notification.permission === "granted") {
          new Notification(
            `Новое сообщение от ${e.message.sender_name || "Пользователя"}`,
            {
              body: e.message.text,
            }
          );
        }
      }
    }
  );
};

const unsubscribeGlobal = () => {
  if (channel && user.value) {
    window.Echo.leave(`chat.${user.value.id}`);
    channel = null;
  }
};

onMounted(() => {
  if (user.value) {
    fetchConversations();
    subscribeGlobal();

    if (Notification.permission === "default") {
      Notification.requestPermission();
    }
  }

  // Инициализируем геолокацию
  initGeolocation();
});

onUnmounted(() => {
  unsubscribeGlobal();
});
</script>

<template>
  <div v-show="show" @click="show = false" class="fixed inset-0 z-40"></div>

  <Header>
    <!-- Категории -->
    <template #categories>
      <CategoriesMenu />
    </template>

    <!-- Авторизация/Профиль -->
    <template #auth="{ user }">
      <div v-if="user" class="relative flex items-center gap-3">
        <button
          @click="openChatModal"
          class="relative hover:bg-slate-50 w-8 h-8 grid place-items-center rounded-full hover:outline outline-1 outline-slate-300 transition text-slate-600"
        >
          <i class="fa-regular fa-envelope"></i>
          <span
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse"
          >
            {{ unreadCount > 9 ? "9+" : unreadCount }}
          </span>
        </button>

        <div
          @click="show = !show"
          class="flex items-center gap-2 px-3 py-1 rounded-lg hover:bg-slate-50 cursor-pointer transition border border-slate-200"
          :class="{ 'bg-slate-50': show }"
        >
          <p class="text-sm">{{ user.name }}</p>
          <i
            class="fa-solid fa-angle-down transition-transform text-xs text-slate-600"
            :class="{ 'rotate-180': show }"
          ></i>
        </div>

        <Link
          v-if="user.role === 'admin'"
          :href="route('admin.index')"
          class="hover:bg-slate-50 w-6 h-6 grid place-items-center rounded-full hover:outline outline-1 outline-slate-300 text-slate-500"
        >
          <i class="fa-solid fa-lock text-sm"></i>
        </Link>

        <div
          v-show="show"
          @click="show = false"
          class="absolute z-50 top-12 right-0 bg-white text-slate-800 rounded-lg border-2 border-slate-200 shadow-xl overflow-hidden w-48"
        >
          <Link
            :href="route('listing.create')"
            class="block w-full px-4 py-2.5 hover:bg-slate-50 text-left border-b border-slate-100 text-sm"
          >
            Новое объявление
          </Link>
          <Link
            :href="route('orders.index')"
            class="block w-full px-4 py-2.5 hover:bg-slate-50 text-left text-sm"
          >
            <i class="fa-regular fa-handshake mr-2"></i>
            Мои сделки
          </Link>
          <Link
            :href="route('profile.show', user.id)"
            class="block w-full px-4 py-2.5 hover:bg-slate-50 text-left text-sm"
          >
            Профиль
          </Link>

          <button
            @click="openChatModal"
            class="block w-full px-4 py-2.5 hover:bg-slate-50 text-left relative text-sm"
          >
            Сообщения
            <span
              v-if="unreadCount > 0"
              class="ml-2 bg-red-500 text-white text-[10px] rounded-full px-1.5 py-0.5"
            >
              {{ unreadCount }}
            </span>
          </button>

          <Link
            :href="route('dashboard')"
            class="block w-full px-4 py-2.5 hover:bg-slate-50 text-left text-sm"
          >
            Мои объявления
          </Link>
          <Link
            :href="route('favorites.index')"
            class="block w-full px-4 py-2.5 hover:bg-slate-50 text-left text-sm"
          >
            <i class="fa-regular fa-heart mr-2"></i>
            Избранное
          </Link>

          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="block w-full px-4 py-2.5 hover:bg-slate-50 text-left text-red-500 text-sm"
          >
            Выйти
          </Link>
        </div>
      </div>

      <div v-else class="flex items-center space-x-4 text-sm">
        <NavLink :href="route('login')" componentName="Auth/Login">
          Авторизация
        </NavLink>
        <NavLink :href="route('register')" componentName="Auth/Register">
          Регистрация
        </NavLink>
      </div>

      <button
        @click="switchTheme"
        class="hover:bg-slate-50 w-8 h-8 grid place-items-center rounded-full hover:outline outline-1 outline-slate-300 transition text-slate-600"
      >
        <i class="fa-solid fa-circle-half-stroke text-sm"></i>
      </button>
    </template>
  </Header>

  <main class="p-6 mx-auto max-w-[1400px] bg-white">
    <slot />
  </main>

  <ChatModal
    :show="showChatModal"
    :conversations="conversations"
    @close="closeChatModal"
  />
</template>