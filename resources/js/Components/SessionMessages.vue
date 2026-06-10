<script setup>
defineProps({
  status: String,
});
</script>

<template>
  <div
    v-if="status"
    class="mb-4 font-medium text-sm text-green-500 bg-green-50 border border-green-200 p-3 rounded-lg"
  >
    {{ status }}
  </div>
</template>