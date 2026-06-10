<template>
  <div v-if="show" class="fixed top-4 right-4 z-50 max-w-sm w-full">
    <div
      class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-l-4 p-4"
      :class="{
        'border-green-500': type === 'success',
        'border-blue-500': type === 'info',
        'border-yellow-500': type === 'warning',
      }"
    >
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <i
            v-if="type === 'success'"
            class="fa-regular fa-circle-check text-green-500 text-xl"
          ></i>
          <i
            v-else-if="type === 'info'"
            class="fa-regular fa-circle-info text-blue-500 text-xl"
          ></i>
          <i
            v-else-if="type === 'warning'"
            class="fa-regular fa-triangle-exclamation text-yellow-500 text-xl"
          ></i>
        </div>
        <div class="ml-3 flex-1">
          <p class="text-sm font-medium text-gray-900 dark:text-white">
            {{ title }}
          </p>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ message }}
          </p>
          <div v-if="link" class="mt-3">
            <Link
              :href="link"
              class="text-sm font-medium text-blue-600 hover:text-blue-800"
            >
              {{ linkText }}
            </Link>
          </div>
        </div>
        <button
          @click="show = false"
          class="ml-4 text-gray-400 hover:text-gray-500"
        >
          <i class="fa-regular fa-xmark"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  show: Boolean,
  title: String,
  message: String,
  type: {
    type: String,
    default: "info",
  },
  link: String,
  linkText: String,
});

const show = ref(props.show);

watch(
  () => props.show,
  (newVal) => {
    show.value = newVal;
    if (newVal) {
      setTimeout(() => {
        show.value = false;
      }, 5000);
    }
  }
);
</script>