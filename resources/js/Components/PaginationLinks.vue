<script setup>
defineProps({
  paginator: Object,
});

const makeLabel = (label) => {
  if (label.includes("Previous")) {
    return "<<";
  } else if (label.includes("Next")) {
    return ">>";
  } else {
    return label;
  }
};
</script>

<template>
  <div class="flex justify-between items-start">
    <div
      class="flex items-center rounded-md overflow-hidden shadow-lg border border-blue-200"
    >
      <div v-for="(link, i) in paginator.links" :key="i">
        <component
          :is="link.url ? 'Link' : 'span'"
          :href="link.url"
          v-html="makeLabel(link.label)"
          class="border-x border-blue-100 w-12 h-12 grid place-items-center"
          :class="{
            'bg-white text-blue-600 hover:bg-blue-50': link.url && !link.active,
            'bg-white text-slate-300 cursor-not-allowed': !link.url,
            'bg-blue-500 text-white font-bold': link.active,
          }"
        />
      </div>
    </div>

    <p class="text-slate-600 text-sm">
      Отображается {{ paginator.from }} до {{ paginator.to }} из
      {{ paginator.total }} результатов
    </p>
  </div>
</template>