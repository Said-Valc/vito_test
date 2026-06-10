<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";

const showMenu = ref(false);
const activeCategory = ref(null);
const categories = ref({});
const menuRef = ref(null);
const timeoutId = ref(null);

// Загружаем категории
const loadCategories = async () => {
  try {
    const response = await axios.get("/api/categories");
    categories.value = response.data;
    console.log("Категории загружены:", response.data);
  } catch (error) {
    console.error("Ошибка загрузки категорий:", error);
  }
};

// Показ/скрытие меню
const toggleMenu = () => {
  showMenu.value = !showMenu.value;
  if (showMenu.value) {
    loadCategories();
  }
};

// Закрытие меню при клике вне
const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    showMenu.value = false;
    activeCategory.value = null;
  }
};

// Обработка наведения с задержкой
const onMouseEnter = (category) => {
  if (timeoutId.value) clearTimeout(timeoutId.value);
  activeCategory.value = category;
};

const onMouseLeave = () => {
  timeoutId.value = setTimeout(() => {
    activeCategory.value = null;
  }, 300);
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  if (timeoutId.value) clearTimeout(timeoutId.value);
});

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

// Цвета иконок для категорий
const categoryIconColors = {
  auto: "text-blue-500",
  nedvizhimost: "text-green-500",
  rabota: "text-red-500",
  uslugi: "text-purple-500",
  lichnie_veschi: "text-pink-500",
  dlya_doma: "text-yellow-500",
  elektronika: "text-indigo-500",
  hobby: "text-orange-500",
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

// Группы для недвижимости
const nedvizhimostGroups = [
  { key: "kvartiry", name: "Квартиры", icon: "fa-building" },
  { key: "komnaty", name: "Комнаты", icon: "fa-door-open" },
  { key: "doma", name: "Дома", icon: "fa-house" },
  { key: "commercial", name: "Коммерческая", icon: "fa-store" },
  { key: "land", name: "Земельные участки", icon: "fa-tree" },
];

// Функция для определения группы подкатегории по имени
const getGroupFromName = (name) => {
  if (name.includes("квартир")) return "kvartiry";
  if (name.includes("комнат")) return "komnaty";
  if (name.includes("Дом") || name.includes("дом")) return "doma";
  if (name.includes("коммерческ")) return "commercial";
  if (name.includes("Земельн") || name.includes("участ")) return "land";
  return "other";
};

// Отфильтрованные подкатегории по группе
const getSubcategoriesByGroup = (group) => {
  if (!categories.value.nedvizhimost) return [];

  return categories.value.nedvizhimost.filter((sub) => {
    const subGroup = getGroupFromName(sub.name);
    return subGroup === group.key;
  });
};

// Функция для безопасного отображения action
const getActionDisplay = (sub) => {
  if (!sub || !sub.action) return null;

  if (Array.isArray(sub.action)) {
    return {
      first: sub.action[0],
      count: sub.action.length - 1,
    };
  }

  return {
    first: sub.action,
    count: 0,
  };
};

// Все подкатегории для активной категории
const activeSubcategories = computed(() => {
  if (!activeCategory.value || !categories.value[activeCategory.value]) {
    return [];
  }
  return categories.value[activeCategory.value];
});
</script>

<template>
  <div class="relative" ref="menuRef">
    <!-- Кнопка Категории -->
    <button
      @click="toggleMenu"
      class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors duration-200 border border-blue-200 text-slate-700"
      :class="{ 'bg-blue-50 border-blue-300': showMenu }"
    >
      <i class="fa-solid fa-bars"></i>
      <span>Категории</span>
      <i
        class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
        :class="{ 'rotate-180': showMenu }"
      ></i>
    </button>

    <!-- Оверлей -->
    <div
      v-show="showMenu"
      class="fixed inset-0 bg-black/20 z-40"
      @click="showMenu = false"
    ></div>

    <!-- Выпадающее меню на всю ширину -->
    <div
      v-show="showMenu"
      class="fixed left-0 right-0 z-50 bg-white shadow-xl border-b-2 border-blue-200"
      style="top: 73px"
      @mouseleave="onMouseLeave"
    >
      <div class="max-w-[1400px] mx-auto p-6">
        <div class="flex gap-6">
          <!-- Основные категории (слева) -->
          <div class="w-1/4 border-r border-blue-100 pr-4">
            <h3 class="text-sm font-semibold text-slate-400 uppercase mb-4">
              Категории
            </h3>
            <div class="space-y-1">
              <div
                v-for="(subcategories, key) in categories"
                :key="key"
                @mouseenter="onMouseEnter(key)"
                class="flex items-center justify-between px-4 py-3 rounded-lg cursor-pointer transition-colors duration-150"
                :class="{
                  'bg-blue-50 border border-blue-200': activeCategory === key,
                  'hover:bg-gray-50 border border-transparent':
                    activeCategory !== key,
                }"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"
                  >
                    <i
                      :class="[
                        'fa-solid',
                        categoryIcons[key],
                        'text-lg',
                        categoryIconColors[key],
                      ]"
                    ></i>
                  </div>
                  <span class="font-medium text-sm">{{
                    categoryNames[key]
                  }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-400">{{
                    subcategories?.length || 0
                  }}</span>
                  <i
                    class="fa-solid fa-chevron-right text-xs text-slate-300"
                  ></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Подкатегории (справа) -->
          <div class="w-3/4">
            <!-- Для недвижимости показываем с группировкой -->
            <template v-if="activeCategory === 'nedvizhimost'">
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                >
                  <i class="fa-solid fa-building text-2xl text-green-500"></i>
                </div>
                <div>
                  <h3 class="font-bold text-lg text-slate-800">Недвижимость</h3>
                  <p class="text-sm text-slate-500">Выберите подкатегорию</p>
                </div>
              </div>

              <div class="grid grid-cols-3 gap-6">
                <div v-for="group in nedvizhimostGroups" :key="group.key">
                  <h4
                    class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2"
                  >
                    <i :class="['fa-solid', group.icon, 'text-blue-500']"></i>
                    {{ group.name }}
                  </h4>
                  <div class="space-y-1">
                    <Link
                      v-for="sub in getSubcategoriesByGroup(group)"
                      :key="sub.id"
                      :href="
                        route('category.show', {
                          type: 'nedvizhimost',
                          id: sub.id,
                        })
                      "
                      class="block px-3 py-2 rounded-lg hover:bg-blue-50 transition-colors duration-150 text-sm text-slate-600 hover:text-blue-600"
                      @click="showMenu = false"
                    >
                      {{ sub.name }}
                    </Link>
                  </div>
                </div>
              </div>
            </template>

            <!-- Для остальных категорий показываем сетку -->
            <template
              v-else-if="activeCategory && activeSubcategories.length > 0"
            >
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center"
                >
                  <i
                    :class="[
                      'fa-solid',
                      categoryIcons[activeCategory],
                      'text-2xl',
                      categoryIconColors[activeCategory],
                    ]"
                  ></i>
                </div>
                <div>
                  <h3 class="font-bold text-lg text-slate-800">
                    {{ categoryNames[activeCategory] }}
                  </h3>
                  <p class="text-sm text-slate-500">Выберите подкатегорию</p>
                </div>
              </div>

              <div class="grid grid-cols-3 gap-2">
                <Link
                  v-for="sub in activeSubcategories"
                  :key="sub.id"
                  :href="
                    route('category.show', { type: activeCategory, id: sub.id })
                  "
                  class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition-colors duration-150 group"
                  @click="showMenu = false"
                >
                  <div
                    class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors"
                  >
                    <i
                      :class="[
                        'fa-solid',
                        sub.icon || 'fa-tag',
                        'text-blue-500',
                      ]"
                    ></i>
                  </div>
                  <div class="flex-1">
                    <span
                      class="text-sm text-slate-700 group-hover:text-blue-600"
                      >{{ sub.name }}</span
                    >
                  </div>
                  <div v-if="sub.action" class="flex gap-1">
                    <template v-if="Array.isArray(sub.action)">
                      <span
                        class="text-xs px-2 py-1 bg-blue-100 text-blue-600 rounded-full"
                      >
                        {{ sub.action[0] }}
                      </span>
                      <span
                        v-if="sub.action.length > 1"
                        class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full"
                      >
                        +{{ sub.action.length - 1 }}
                      </span>
                    </template>
                    <span
                      v-else
                      class="text-xs px-2 py-1 bg-blue-100 text-blue-600 rounded-full"
                    >
                      {{ sub.action }}
                    </span>
                  </div>
                  <i
                    class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-blue-400"
                  ></i>
                </Link>
              </div>
            </template>

            <!-- Подсказка когда не наведена категория -->
            <div
              v-else
              class="flex items-center justify-center h-full min-h-[300px]"
            >
              <div class="text-center">
                <div
                  class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4"
                >
                  <i
                    class="fa-solid fa-arrow-pointer text-3xl text-blue-400"
                  ></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-2">
                  Выберите категорию
                </h3>
                <p class="text-slate-500">
                  Наведите на категорию слева,<br />чтобы увидеть подкатегории
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>