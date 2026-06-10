<script setup>
import { ref, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";

const categories = ref([]);
const isLoading = ref(true);

// Загружаем категории
const loadCategories = async () => {
  try {
    const response = await axios.get("/api/categories");
    const data = response.data;

    categories.value = formatCategories(data);
  } catch (error) {
    console.error("Ошибка загрузки категорий:", error);
  } finally {
    isLoading.value = false;
  }
};

// Иконки для категорий
const categoryIcons = {
  auto: "fa-car",
  nedvizhimost: "fa-building",
  rabota: "fa-briefcase",
  uslugi: "fa-handshake",
  lichnie_veschi: "fa-shirt",
  dlya_doma: "fa-home",
  elektronika: "fa-laptop",
  hobby: "fa-futbol",
};

// Русские названия категорий
const categoryNames = {
  auto: "Авто",
  nedvizhimost: "Недвижимость",
  rabota: "Работа",
  uslugi: "Услуги",
  lichnie_veschi: "Личные вещи",
  dlya_doma: "Для дома",
  elektronika: "Электроника",
  hobby: "Хобби и отдых",
};

// Количество объявлений в категории
const getCountText = (count) => {
  if (!count || count === 0) return "0";

  return `${count}`;
};

// Форматируем данные категорий
const formatCategories = (data) => {
  return Object.entries(data).map(([key, subcategories]) => ({
    key,
    name: categoryNames[key] || key,
    icon: categoryIcons[key] || "fa-folder",
    count: Array.isArray(subcategories) ? subcategories.length : 0,
    firstSubcategory:
      Array.isArray(subcategories) && subcategories[0]
        ? subcategories[0].id
        : null,
  }));
};

// Получаем ссылку для категории
const getCategoryLink = (category) => {
  if (category.firstSubcategory) {
    return route("category.show", {
      type: category.key,
      id: category.firstSubcategory,
    });
  }
  return route("home");
};

onMounted(() => {
  loadCategories();
});
</script>

<template>
  <div v-if="!isLoading && categories.length > 0" class="mb-8">
    <div class="flex flex-wrap gap-3">
      <Link
        v-for="category in categories"
        :key="category.key"
        :href="getCategoryLink(category)"
        class="w-[150px] h-[90px] bg-gray-100 hover:bg-blue-50 border-2 border-gray-200 hover:border-blue-300 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-1.5 px-3 group cursor-pointer"
      >
        <!-- Иконка -->
        <div
          class="flex-shrink-0 w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:bg-blue-500 transition-colors duration-300"
        >
          <i
            :class="[
              'fa-solid',
              category.icon,
              'text-base text-blue-500 group-hover:text-white transition-colors duration-300',
            ]"
          ></i>
        </div>

        <!-- Текст -->
        <div class="text-center w-full">
          <h3
            class="font-medium text-xs text-slate-800 group-hover:text-blue-600 transition-colors duration-300 truncate"
          >
            {{ category.name }}
          </h3>
          <p
            class="text-xs text-slate-500 group-hover:text-blue-500 transition-colors duration-300"
          >
            {{ getCountText(category.count) }}
          </p>
        </div>
      </Link>
    </div>
  </div>

  <!-- Загрузка -->
  <div v-else-if="isLoading" class="mb-8">
    <div class="flex flex-wrap gap-3">
      <div
        v-for="i in 8"
        :key="i"
        class="w-[150px] h-[90px] rounded-xl bg-gray-100 animate-pulse"
      ></div>
    </div>
  </div>
</template>