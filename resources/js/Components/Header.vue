<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import NavLink from "./NavLink.vue";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const user = computed(() => page.props.auth.user);

// Поиск
const searchQuery = ref("");
const showSearch = ref(false);

const search = () => {
  if (searchQuery.value.trim()) {
    window.location.href = route("home", { search: searchQuery.value });
  }
};

// Закрытие поиска по Escape
const handleEscape = (e) => {
  if (e.key === "Escape") {
    showSearch.value = false;
    searchQuery.value = "";
  }
};

onMounted(() => {
  document.addEventListener("keydown", handleEscape);
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleEscape);
});
</script>

<template>
  <header class="bg-white border-b-2 border-blue-500 text-slate-800 shadow-sm sticky top-0 z-50">
    <nav class="px-6 py-4 mx-auto max-w-[1400px]">
      <div class="flex items-center justify-between gap-6">
        <!-- Левая часть: Главная + Категории -->
        <div class="flex items-center space-x-4">
          <NavLink :href="route('home')" componentName="Home" class="text-lg font-semibold">
            Главная
          </NavLink>
          
          <div class="h-6 w-px bg-blue-200"></div>
          
          <slot name="categories">
            <!-- Категории будут вставлены здесь -->
          </slot>
        </div>

        <!-- Центр: Поиск -->
        <div class="flex-1 max-w-2xl mx-4">
          <form @submit.prevent="search" class="w-full">
            <div class="flex rounded-lg border border-blue-300 overflow-hidden shadow-sm">
              <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <i class="fa-solid fa-magnifying-glass text-blue-400 text-sm"></i>
                </div>
                <input
                  type="search"
                  v-model="searchQuery"
                  placeholder="Поиск объявлений..."
                  class="block w-full pl-10 pr-4 py-2 text-sm text-slate-900 border-0 outline-0 focus:ring-0 bg-white placeholder:text-slate-400"
                />
              </div>
              <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 text-sm font-medium hover:bg-blue-600 transition-colors duration-200 flex items-center gap-1.5"
              >
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                <span>Найти</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Правая часть: Авторизация/Профиль -->
        <div class="flex items-center space-x-4">
          <slot name="auth" :user="user">
            <!-- Авторизация будет вставлена здесь -->
          </slot>
        </div>
      </div>
    </nav>
  </header>
</template>