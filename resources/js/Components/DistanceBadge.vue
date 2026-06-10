<template>
  <div
    v-if="formattedDistance"
    class="distance-badge inline-flex items-center gap-1 text-xs font-medium"
    :class="colorClass"
    :title="`Расстояние от вас: ${formattedDistance}`"
  >
    <i class="fa-solid fa-location-dot"></i>
    <span>{{ formattedDistance }}</span>
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useGeolocation } from "@/composables/useGeolocation";

const props = defineProps({
  latitude: {
    type: [Number, String],
    default: null,
  },
  longitude: {
    type: [Number, String],
    default: null,
  },
  size: {
    type: String,
    default: "sm",
  },
});

const { userLocation, calculateDistance, formatDistance, init } =
  useGeolocation();

// Инициализируем геолокацию при монтировании
onMounted(() => {
  if (!userLocation.value) {
    init();
  }
});

const distance = computed(() => {
  if (!userLocation.value || !props.latitude || !props.longitude) {
    return null;
  }

  return calculateDistance(
    userLocation.value.latitude,
    userLocation.value.longitude,
    Number(props.latitude),
    Number(props.longitude)
  );
});

const formattedDistance = computed(() => {
  return formatDistance(distance.value);
});

const colorClass = computed(() => {
  if (distance.value === null)
    return "text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full";
  if (distance.value < 1)
    return "text-green-600 bg-green-50 px-2 py-0.5 rounded-full";
  if (distance.value < 5)
    return "text-green-500 bg-green-50 px-2 py-0.5 rounded-full";
  if (distance.value < 20)
    return "text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full";
  if (distance.value < 100)
    return "text-slate-500 bg-slate-50 px-2 py-0.5 rounded-full";
  return "text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full";
});
</script>

<style scoped>
.distance-badge {
  white-space: nowrap;
}
</style>