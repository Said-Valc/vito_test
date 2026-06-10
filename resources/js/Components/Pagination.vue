<template>
  <div v-if="links.length > 3" class="flex flex-wrap gap-2">
    <template v-for="(link, key) in links" :key="key">
      <button
        v-if="link.url === null"
        class="px-3 py-1 text-sm bg-white text-slate-300 border border-blue-100 rounded cursor-not-allowed"
        v-html="link.label"
        disabled
      />
      <Link
        v-else
        :href="link.url"
        class="px-3 py-1 text-sm rounded transition border"
        :class="
          link.active
            ? 'bg-blue-500 text-white border-blue-500'
            : 'bg-white text-blue-600 border-blue-200 hover:bg-blue-50 hover:border-blue-300'
        "
        v-html="link.label"
        preserve-scroll
      />
    </template>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  links: {
    type: Array,
    required: true,
  },
});
</script>