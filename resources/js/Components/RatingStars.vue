<template>
  <div class="flex items-center gap-1">
    <!-- Звезды -->
    <div class="flex">
      <i
        v-for="star in 5"
        :key="star"
        :class="getStarClass(star)"
        :style="{ fontSize: size + 'px' }"
      ></i>
    </div>

    <!-- Числовое значение -->
    <span
      v-if="showValue && rating > 0"
      :class="['ml-1 font-semibold', textClass]"
      :style="{ fontSize: size * 0.875 + 'px' }"
    >
      {{ rating.toFixed(1) }}
    </span>

    <!-- Количество отзывов -->
    <span
      v-if="showCount && count > 0"
      :class="['ml-1', textClass]"
      :style="{ fontSize: size * 0.75 + 'px' }"
    >
      ({{ count }} {{ getDeclension(count) }})
    </span>

    <!-- Нет отзывов -->
    <span
      v-if="count === 0 && showEmpty"
      :class="['ml-1', textClass]"
      :style="{ fontSize: size * 0.75 + 'px' }"
    >
      Нет отзывов
    </span>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  rating: {
    type: Number,
    default: 0,
  },
  count: {
    type: Number,
    default: 0,
  },
  size: {
    type: Number,
    default: 14,
  },
  showValue: {
    type: Boolean,
    default: true,
  },
  showCount: {
    type: Boolean,
    default: true,
  },
  showEmpty: {
    type: Boolean,
    default: false,
  },
  textClass: {
    type: String,
    default: "text-gray-600 dark:text-gray-400",
  },
});

const getStarClass = (star) => {
  const fullStars = Math.floor(props.rating);
  const hasHalfStar = props.rating % 1 >= 0.5;

  if (star <= fullStars) {
    return "fa-solid fa-star text-yellow-400";
  } else if (star === fullStars + 1 && hasHalfStar) {
    return "fa-regular fa-star-half-stroke text-yellow-400";
  } else {
    return "fa-regular fa-star text-gray-300 dark:text-gray-600";
  }
};

const getDeclension = (number) => {
  const titles = ["отзыв", "отзыва", "отзывов"];
  const cases = [2, 0, 1, 1, 1, 2];
  return titles[
    number % 100 > 4 && number % 100 < 20
      ? 2
      : cases[number % 10 < 5 ? number % 10 : 5]
  ];
};
</script>