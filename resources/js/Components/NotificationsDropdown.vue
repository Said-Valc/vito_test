<template>
  <div class="relative">
    <button
      @click="show = !show"
      class="relative hover:bg-slate-700 w-8 h-8 grid place-items-center rounded-full hover:outline outline-1 outline-white transition"
    >
      <i class="fa-regular fa-bell"></i>
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse"
      >
        {{ unreadCount > 9 ? "9+" : unreadCount }}
      </span>
    </button>

    <div
      v-if="show"
      class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border dark:border-gray-700 z-50"
    >
      <div class="p-3 border-b dark:border-gray-700">
        <h3 class="font-semibold">Уведомления</h3>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <div
          v-if="notifications.length === 0"
          class="p-4 text-center text-gray-500"
        >
          Нет уведомлений
        </div>

        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="p-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
          :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.read_at }"
          @click="handleNotificationClick(notification)"
        >
          <p class="text-sm font-medium">
            {{ notification.data.title || "Новая сделка" }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ notification.data.message }}
          </p>
          <p class="text-xs text-gray-400 mt-1">
            {{ formatTime(notification.created_at) }}
          </p>
        </div>
      </div>

      <div
        v-if="notifications.length > 0"
        class="p-2 border-t dark:border-gray-700"
      >
        <button
          @click="markAllAsRead"
          class="w-full text-center text-sm text-blue-600 hover:text-blue-800 py-1"
        >
          Отметить все как прочитанные
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

const show = ref(false);
const notifications = ref([]);

const unreadCount = computed(() => {
  return notifications.value.filter((n) => !n.read_at).length;
});

const formatTime = (date) => {
  return new Date(date).toLocaleString("ru-RU");
};

const fetchNotifications = async () => {
  try {
    const { data } = await axios.get("/api/notifications");
    notifications.value = data;
  } catch (error) {
    console.error("Error fetching notifications:", error);
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.read_at) {
    try {
      await axios.post(`/api/notifications/${notification.id}/mark-as-read`);
      notification.read_at = new Date().toISOString();
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  }

  if (notification.data.link) {
    router.visit(notification.data.link);
  } else {
    router.visit(route("orders.index"));
  }

  show.value = false;
};

const markAllAsRead = async () => {
  try {
    await axios.post("/api/notifications/mark-all-as-read");
    notifications.value.forEach((n) => (n.read_at = new Date().toISOString()));
  } catch (error) {
    console.error("Error marking all as read:", error);
  }
};

onMounted(() => {
  fetchNotifications();

  // Подписка на новые уведомления через WebSocket
  if (window.Echo) {
    window.Echo.private(
      `App.Models.User.${window.Laravel.user.id}`
    ).notification((notification) => {
      notifications.value.unshift({
        id: Date.now(),
        data: notification,
        created_at: new Date().toISOString(),
        read_at: null,
      });
    });
  }
});
</script>