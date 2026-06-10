<script setup>
defineProps({
  routeName: String,
  componentName: String,
});
</script>
<template>
  <Link
    :href="route(routeName)"
    class="px-3 py-2 rounded-lg hover:bg-blue-50 border border-transparent hover:border-blue-200 transition-colors text-slate-700"
    :class="{
      'bg-blue-50 border-blue-300 text-blue-600 font-medium':
        $page.component === componentName,
    }"
  >
    <slot />
  </Link>
</template>