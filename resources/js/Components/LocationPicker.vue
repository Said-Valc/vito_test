<template>
  <div class="location-picker">
    <div class="relative">
      <!-- Поле ввода локации -->
      <div class="relative flex gap-2">
        <div class="flex-1 relative">
          <input
            ref="inputRef"
            type="text"
            :value="displayValue"
            @input="onInput"
            @focus="onFocus"
            @blur="onBlur"
            :placeholder="placeholder"
            :class="[
              'w-full px-4 py-2.5 border-2 rounded-lg focus:outline-none focus:ring-2 transition bg-white text-slate-800',
              error
                ? 'border-red-300 focus:ring-red-400 focus:border-red-400'
                : 'border-slate-200 focus:ring-slate-400 focus:border-slate-400',
            ]"
            autocomplete="off"
          />

          <!-- Иконка геолокации -->
          <button
            v-if="!modelValue && !searchQuery"
            @click="detectLocation"
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 transition"
            title="Определить автоматически"
          >
            <i class="fa-solid fa-location-crosshairs"></i>
          </button>

          <!-- Кнопка очистки -->
          <button
            v-if="modelValue"
            @click="clearLocation"
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 transition"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Фильтр по стране -->
        <select
          v-model="selectedCountry"
          @change="onCountryChange"
          class="px-3 py-2.5 border-2 border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-400 bg-white text-slate-700 text-sm min-w-[120px]"
        >
          <option value="">Все страны</option>
          <option value="Россия">🇷🇺 Россия</option>
          <option value="Турция">🇹🇷 Турция</option>
        </select>
      </div>

      <!-- Выпадающий список подсказок -->
      <div
        v-if="showSuggestions && suggestions.length > 0"
        class="absolute z-50 w-full mt-1 bg-white border-2 border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
      >
        <div
          class="p-2 border-b border-slate-100 bg-slate-50 flex items-center justify-between"
        >
          <p class="text-xs text-slate-500 font-medium">
            {{ detecting ? "Определение..." : "Выберите город" }}
          </p>
          <span class="text-xs text-slate-400"
            >{{ suggestions.length }} городов</span
          >
        </div>

        <div
          v-for="location in suggestions"
          :key="location.id"
          @mousedown.prevent="selectLocation(location)"
          class="px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-50 last:border-b-0"
        >
          <div class="flex items-center justify-between">
            <div>
              <div class="font-medium text-slate-800">
                {{ location.name }}
              </div>
              <div class="text-sm text-slate-500">
                {{ location.region }}
              </div>
            </div>
            <div class="text-right">
              <span class="text-xs text-slate-400">{{ location.country }}</span>
              <div v-if="location.distance" class="text-xs text-green-500 mt-1">
                {{ location.distance }} км
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Сообщение об обнаружении локации -->
      <div
        v-if="detectedLocation && !modelValue && !showSuggestions"
        class="absolute z-50 w-full mt-1 bg-white border-2 border-green-200 rounded-lg shadow-lg p-4"
      >
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm text-slate-600 mb-2">
              <i class="fa-solid fa-location-dot text-green-500 mr-1"></i>
              Мы определили ваш город
            </p>
            <p class="font-medium text-slate-800">
              {{ detectedLocation.full_name }}
            </p>
            <p class="text-xs text-slate-400 mt-1">
              {{ detectedLocation.country }}
            </p>
          </div>
          <div class="flex gap-2 ml-4">
            <button
              @click="selectLocation(detectedLocation)"
              class="px-3 py-1.5 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition"
            >
              Да, верно
            </button>
            <button
              @click="detectedLocation = null"
              class="px-3 py-1.5 border-2 border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 transition"
            >
              Нет
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Сообщение об ошибке -->
    <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
  modelValue: {
    type: Object,
    default: null,
  },
  placeholder: {
    type: String,
    default: "Начните вводить город...",
  },
  error: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue", "location-selected"]);

const inputRef = ref(null);
const searchQuery = ref("");
const suggestions = ref([]);
const showSuggestions = ref(false);
const detectedLocation = ref(null);
const detecting = ref(false);
const isFocused = ref(false);
const selectedCountry = ref("");

// Отображаемое значение
const displayValue = computed(() => {
  if (props.modelValue) {
    return props.modelValue.full_name || props.modelValue.name;
  }
  return searchQuery.value;
});

// Поиск локаций с debounce
let searchTimeout;
const onInput = (e) => {
  searchQuery.value = e.target.value;

  if (props.modelValue) {
    emit("update:modelValue", null);
    emit("location-selected", null);
  }

  clearTimeout(searchTimeout);

  if (searchQuery.value.length >= 2) {
    searchTimeout = setTimeout(() => {
      searchLocations(searchQuery.value);
    }, 300);
  } else {
    suggestions.value = [];
    showSuggestions.value = false;
  }
};

const onFocus = () => {
  isFocused.value = true;
  if (searchQuery.value.length >= 2) {
    showSuggestions.value = true;
  }
};

const onBlur = () => {
  setTimeout(() => {
    isFocused.value = false;
    showSuggestions.value = false;
  }, 200);
};

const onCountryChange = () => {
  if (searchQuery.value.length >= 2) {
    searchLocations(searchQuery.value);
  }
};

// Поиск локаций
const searchLocations = async (query) => {
  try {
    const params = { q: query };
    if (selectedCountry.value) {
      params.country = selectedCountry.value;
    }

    const { data } = await axios.get("/api/v1/locations/search", { params });
    suggestions.value = data.data;
    showSuggestions.value = true;
  } catch (error) {
    console.error("Search locations error:", error);
  }
};

// Выбор локации
const selectLocation = (location) => {
  emit("update:modelValue", location);
  emit("location-selected", location);
  searchQuery.value = "";
  suggestions.value = [];
  showSuggestions.value = false;
  detectedLocation.value = null;
};

// Очистка локации
const clearLocation = () => {
  emit("update:modelValue", null);
  emit("location-selected", null);
  searchQuery.value = "";
  suggestions.value = [];
};

// Автоматическое определение локации
const detectLocation = async () => {
  detecting.value = true;

  try {
    // Пробуем получить геолокацию браузера
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const { data } = await axios.get("/api/v1/locations/nearest", {
            params: {
              latitude: position.coords.latitude,
              longitude: position.coords.longitude,
            },
          });

          if (data.data && data.data.length > 0) {
            // Берем ближайший город, учитывая фильтр страны
            const nearest = selectedCountry.value
              ? data.data.find((l) => l.country === selectedCountry.value) ||
                data.data[0]
              : data.data[0];
            detectedLocation.value = nearest;
          }
          detecting.value = false;
        },
        async () => {
          await detectByIp();
          detecting.value = false;
        }
      );
    } else {
      await detectByIp();
      detecting.value = false;
    }
  } catch (error) {
    console.error("Detect location error:", error);
    detecting.value = false;
  }
};

// Определение по IP
const detectByIp = async () => {
  try {
    const { data } = await axios.get("/api/v1/locations/detect", {
      params: { country: selectedCountry.value },
    });
    if (data.data) {
      detectedLocation.value = data.data;
    }
  } catch (error) {
    console.error("IP detection error:", error);
  }
};

// Автоопределение при монтировании
onMounted(() => {
  if (!props.modelValue) {
    setTimeout(() => {
      detectLocation();
    }, 1000);
  }
});

// Следим за значением
watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) {
      searchQuery.value = "";
    }
  }
);
</script>