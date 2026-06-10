<script setup>
import { ref, watch } from "vue";

const emit = defineEmits(["images"]);

const props = defineProps({
  listingImages: {
    type: Array,
    default: () => [],
  },
});

const previews = ref([]);
const files = ref([]);
const oversized = ref(false);

// Наблюдаем за существующими изображениями (при редактировании)
watch(
  () => props.listingImages,
  (images) => {
    // Проверяем, что images существует и это массив
    if (images && Array.isArray(images) && images.length > 0) {
      // Если это объекты с path (существующие изображения)
      if (images[0].path) {
        previews.value = images.map((img) => `/storage/${img.path}`);
      }
      // Если это File объекты (только что выбранные)
      else if (images[0] instanceof File) {
        previews.value = images.map((file) => URL.createObjectURL(file));
        files.value = images;
      }
    } else {
      // Если images пустой, очищаем превью
      previews.value = [];
      files.value = [];
    }
  },
  { immediate: true, deep: true }
);

const imageSelected = (e) => {
  const selectedFiles = Array.from(e.target.files);

  // Проверяем размер каждого файла
  oversized.value = selectedFiles.some((file) => file.size > 3145728);

  if (oversized.value) return;

  // Добавляем новые файлы к существующим
  const newPreviews = selectedFiles.map((file) => URL.createObjectURL(file));

  previews.value = [...previews.value, ...newPreviews];
  files.value = [...files.value, ...selectedFiles];

  emit("images", files.value);

  // Сбрасываем input, чтобы можно было выбрать те же файлы повторно
  e.target.value = "";
};

const removeImage = (index) => {
  // Освобождаем память от blob URL если это был новый файл
  if (previews.value[index] && previews.value[index].startsWith("blob:")) {
    URL.revokeObjectURL(previews.value[index]);
  }

  previews.value.splice(index, 1);
  files.value.splice(index, 1);

  emit("images", files.value);
};
</script>

<template>
  <div>
    <span
      class="block text-sm font-medium text-slate-700 dark:text-slate-300"
      :class="{ '!text-red-500': oversized }"
    >
      {{
        oversized
          ? "Одно из изображений превышает 3 Мб."
          : `Изображения (${
              files.length > 0
                ? files.length + " выбрано"
                : "можно выбрать несколько"
            }, макс 3 Мб каждое)`
      }}
    </span>

    <!-- Preview Grid -->
    <div v-if="previews.length > 0" class="grid grid-cols-3 gap-3 mt-2">
      <div
        v-for="(img, index) in previews"
        :key="index"
        class="relative h-[120px] bg-slate-300 rounded overflow-hidden group"
      >
        <img
          :src="img"
          class="object-cover object-center h-full w-full"
          :alt="'Изображение ' + (index + 1)"
        />

        <button
          type="button"
          @click="removeImage(index)"
          class="absolute top-1 right-1 bg-red-500/90 text-white w-6 h-6 rounded-full text-xs grid place-items-center opacity-0 group-hover:opacity-100 transition"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- Upload Box -->
    <label
      class="mt-2 h-[120px] bg-slate-200 rounded flex flex-col items-center justify-center cursor-pointer border-2 border-dashed border-slate-300 hover:border-blue-400 hover:bg-slate-100 transition"
    >
      <span class="text-2xl text-slate-400 mb-1">+</span>
      <span class="text-sm text-slate-500">
        {{ previews.length > 0 ? "Добавить ещё" : "Загрузить изображения" }}
      </span>
      <input
        type="file"
        multiple
        hidden
        accept="image/*"
        @input="imageSelected"
      />
    </label>
  </div>
</template>