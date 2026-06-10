<script setup>
const model = defineModel();

defineProps({
  label: String,
  icon: String,
  placeholder: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "text",
  },
});
</script>

<template>
  <div>
    <label :for="label" class="block text-sm font-medium text-slate-700">
      {{ label }}
    </label>

    <div class="relative mt-1 rounded-md">
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
      >
        <span class="grid place-content-center text-sm text-blue-400">
          <i :class="`fa-solid fa-${icon}`"></i>
        </span>
      </div>
      <input
        :type="type"
        :name="label"
        :placeholder="placeholder"
        v-model="model"
        class="block w-full rounded-md pr-3 pl-9 text-sm text-slate-900 border-2 border-blue-200 outline-0 focus:ring-2 focus:ring-inset focus:ring-blue-400 focus:border-blue-400 placeholder:text-slate-400 bg-white"
      />
    </div>
  </div>
</template>