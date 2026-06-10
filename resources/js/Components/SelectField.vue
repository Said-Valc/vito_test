<script setup>
defineProps({
  label: String,
  icon: String,
  modelValue: [String, Number],
  options: Array,
  required: Boolean,
});

defineEmits(["update:modelValue"]);
</script>

<template>
  <div>
    <label
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
    >
      <i v-if="icon" :class="`fa-regular fa-${icon} mr-2`"></i>
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <select
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
      :required="required"
    >
      <option value="">Выберите...</option>
      <option
        v-for="option in options"
        :key="option.value || option"
        :value="option.value || option"
      >
        {{ option.label || option }}
      </option>
    </select>
  </div>
</template>