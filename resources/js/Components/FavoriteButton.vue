<template>
  <button
    @click.stop="toggleFavorite"
    :disabled="loading"
    class="favorite-button transition-all duration-300 transform hover:scale-110 focus:outline-none"
    :class="[
      isFavorite
        ? 'text-red-500 hover:text-red-600'
        : 'text-slate-400 hover:text-red-400',
      size === 'lg' ? 'text-2xl' : size === 'sm' ? 'text-lg' : 'text-xl',
    ]"
    :title="isFavorite ? 'Убрать из избранного' : 'Добавить в избранное'"
  >
    <i
      :class="[
        isFavorite ? 'fa-solid fa-heart' : 'fa-regular fa-heart',
        loading ? 'fa-beat' : '',
      ]"
    ></i>
  </button>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
  listableType: {
    type: String,
    required: true,
  },
  listableId: {
    type: [Number, String],
    required: true,
  },
  size: {
    type: String,
    default: "md", // sm, md, lg
    validator: (value) => ["sm", "md", "lg"].includes(value),
  },
});

const emit = defineEmits(["favorite-toggled"]);

const page = usePage();
const isFavorite = ref(false);
const loading = ref(false);

// Проверяем, авторизован ли пользователь
const isAuthenticated = () => {
  return !!page.props.auth?.user;
};

// Настройка axios с CSRF токеном
axios.defaults.headers.common["X-CSRF-TOKEN"] = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute("content");
axios.defaults.withCredentials = true;

// Проверяем статус избранного при монтировании
onMounted(async () => {
  if (isAuthenticated()) {
    await checkFavoriteStatus();
  }
});

const checkFavoriteStatus = async () => {
  try {
    const response = await axios.get(
      `/api/v1/favorites/check?listable_type=${props.listableType}&listable_id=${props.listableId}`
    );
    isFavorite.value = response.data.is_favorite;
  } catch (error) {
    console.error("Error checking favorite status:", error);
  }
};

const toggleFavorite = async () => {
  if (!isAuthenticated()) {
    // Если пользователь не авторизован, перенаправляем на логин
    window.location.href = "/login";
    return;
  }

  loading.value = true;

  try {
    // Используем правильный URL
    const response = await axios.post("/api/v1/favorites/toggle", {
      listable_type: props.listableType,
      listable_id: props.listableId,
    });

    isFavorite.value = response.data.is_favorite;

    // Оповещаем родительский компонент
    emit("favorite-toggled", {
      isFavorite: isFavorite.value,
      message: response.data.message,
    });
  } catch (error) {
    console.error("Error toggling favorite:", error);

    // Показываем сообщение об ошибке
    if (error.response?.status === 401) {
      window.location.href = "/login";
    } else if (error.response?.status === 404) {
      console.error("API endpoint not found. Check routes/api.php");
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.favorite-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  background: none;
  border: none;
  padding: 4px;
  border-radius: 50%;
}

.favorite-button:hover {
  background-color: rgba(239, 68, 68, 0.1);
}

.favorite-button:active {
  transform: scale(0.95);
}

.favorite-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>