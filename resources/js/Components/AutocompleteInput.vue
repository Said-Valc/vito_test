<template>
  <div class="relative">
    <div class="relative">
      <input
        ref="inputRef"
        type="text"
        :value="modelValue"
        @input="onInput"
        @focus="onFocus"
        @blur="onBlur"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="[
          'w-full px-4 py-2.5 border-2 rounded-lg focus:outline-none focus:ring-2 transition bg-white text-slate-800 pl-10',
          disabled
            ? 'bg-slate-100 border-slate-200 cursor-not-allowed text-slate-400'
            : 'border-slate-200 focus:ring-slate-400 focus:border-slate-400',
        ]"
        autocomplete="off"
      />
      <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
        <i :class="['fa-solid', icon]"></i>
      </div>
      <button
        v-if="modelValue && !disabled"
        @click="clearInput"
        type="button"
        class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-slate-600 transition"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Выпадающий список -->
    <div
      v-if="showSuggestions && !disabled && safeItems.length > 0"
      class="absolute z-50 w-full mt-1 bg-white border-2 border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <div class="p-2 border-b border-slate-100 bg-slate-50">
        <p class="text-xs text-slate-500 font-medium">
          {{ safeItems.length }} {{ itemsLabel }}
        </p>
      </div>
      <div
        v-for="(item, index) in safeItems"
        :key="index"
        @mousedown.prevent="selectItem(item)"
        class="px-4 py-2.5 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-50 last:border-b-0"
        :class="{ 'bg-blue-50': item === modelValue }"
      >
        <span class="text-slate-800" v-html="highlightMatch(item)"></span>
      </div>
    </div>

    <!-- Нет результатов -->
    <div
      v-if="
        showSuggestions &&
        !disabled &&
        modelValue &&
        safeItems.length === 0 &&
        items.length > 0
      "
      class="absolute z-50 w-full mt-1 bg-white border-2 border-slate-200 rounded-lg shadow-lg p-4 text-center"
    >
      <p class="text-sm text-slate-500">
        Ничего не найдено по запросу "{{ modelValue }}"
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  items: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: "Начните вводить...",
  },
  icon: {
    type: String,
    default: "fa-car",
  },
  required: {
    type: Boolean,
    default: false,
  },
  itemsLabel: {
    type: String,
    default: "вариантов",
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "item-selected"]);

const inputRef = ref(null);
const showSuggestions = ref(false);

// Безопасное получение массива
const safeItems = computed(() => {
  const items = props.items;
  if (!items || !Array.isArray(items)) return [];

  if (!props.modelValue) return items;
  const query = String(props.modelValue).toLowerCase();
  return items.filter((item) => String(item).toLowerCase().includes(query));
});

// Подсветка совпадений
const highlightMatch = (text) => {
  if (!props.modelValue) return text;
  const str = String(text);
  const escapedQuery = String(props.modelValue).replace(
    /[.*+?^${}()|[\]\\]/g,
    "\\$&"
  );
  const regex = new RegExp(`(${escapedQuery})`, "gi");
  return str.replace(
    regex,
    '<mark class="bg-yellow-200 text-slate-900 rounded px-0.5">$1</mark>'
  );
};

// Ввод текста
const onInput = (e) => {
  emit("update:modelValue", e.target.value);
  showSuggestions.value = true;
};

// Фокус - показываем список сразу
const onFocus = () => {
  if (!props.disabled) {
    showSuggestions.value = true;
  }
};

// Потеря фокуса
const onBlur = () => {
  setTimeout(() => {
    showSuggestions.value = false;
  }, 200);
};

// Выбор из списка
const selectItem = (item) => {
  emit("update:modelValue", item);
  emit("item-selected", item);
  showSuggestions.value = false;
};

// Очистка
const clearInput = () => {
  emit("update:modelValue", "");
  emit("item-selected", "");
  showSuggestions.value = true;
  inputRef.value?.focus();
};

// Фокус извне
const focus = () => {
  inputRef.value?.focus();
};

defineExpose({ inputRef, focus });
</script>