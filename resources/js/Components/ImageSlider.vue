<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  images: {
    type: [Array, Object],
    default: () => [],
  },
  defaultImage: {
    type: String,
    default: "/images/no-image.jpg",
  },
  containerClass: {
    type: String,
    default: "w-full h-48",
  },
  showIndicators: {
    type: Boolean,
    default: true,
  },
  image: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["image-change"]);

// Нормализуем images в массив строк (путей к файлам)
const normalizedImages = computed(() => {
  let result = [];

  if (Array.isArray(props.images) && props.images.length > 0) {
    // Если пришли полные URL (с http), извлекаем путь
    result = props.images
      .map((img) => {
        if (typeof img === "string") {
          // Если это полный URL, извлекаем путь после /storage/
          const match = img.match(/\/storage\/(.+)$/);
          if (match) {
            return match[1]; // Возвращаем только путь
          }
          return img;
        }
        if (img.path) return img.path;
        return null;
      })
      .filter(Boolean);
  } else if (props.image) {
    const match = props.image.match(/\/storage\/(.+)$/);
    if (match) {
      result = [match[1]];
    } else {
      result = [props.image];
    }
  }

  return result;
});

const currentIndex = ref(0);

// Получаем текущее изображение - добавляем /storage/ к пути
const currentImage = computed(() => {
  const images = normalizedImages.value;

  if (images.length > 0 && images[currentIndex.value]) {
    // Добавляем /storage/ к пути
    return `/storage/${images[currentIndex.value]}`;
  }

  return props.defaultImage;
});

// Есть ли несколько изображений
const hasMultipleImages = computed(() => normalizedImages.value.length > 1);

const handleMouseMove = (e) => {
  if (!hasMultipleImages.value) return;

  const rect = e.currentTarget.getBoundingClientRect();
  const relativeX = e.clientX - rect.left;
  const zoneWidth = rect.width / normalizedImages.value.length;
  const idx = Math.floor(relativeX / zoneWidth);

  if (idx !== currentIndex.value) {
    currentIndex.value = Math.min(idx, normalizedImages.value.length - 1);
    emit("image-change", currentIndex.value);
  }
};

const handleMouseLeave = () => {
  if (!hasMultipleImages.value) return;
  if (currentIndex.value !== 0) {
    currentIndex.value = 0;
    emit("image-change", 0);
  }
};

const setIndex = (index) => {
  if (index >= 0 && index < normalizedImages.value.length) {
    currentIndex.value = index;
  }
};

defineExpose({
  setIndex,
  currentIndex,
});
</script>

<template>
  <div
    class="relative overflow-hidden cursor-pointer"
    :class="containerClass"
    @mousemove="handleMouseMove"
    @mouseleave="handleMouseLeave"
  >
    <img
      :src="currentImage"
      class="w-full h-full object-cover object-center transition-all duration-200"
      alt="Listing Image"
      @error="(e) => (e.target.src = defaultImage)"
    />

    <!-- Индикаторы -->
    <div
      v-if="showIndicators && hasMultipleImages"
      class="absolute bottom-2 left-0 right-0 flex justify-center gap-1 px-2"
    >
      <div
        v-for="(_, idx) in normalizedImages"
        :key="idx"
        class="h-1.5 rounded-full transition-all duration-200"
        :class="[idx === currentIndex ? 'bg-white w-4' : 'bg-white/40 w-1.5']"
      ></div>
    </div>
  </div>
</template>