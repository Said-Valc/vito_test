<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import Card from "@/Components/Card.vue";

const props = defineProps({
  listings: {
    type: Array,
    required: true,
  },
  sellerId: {
    type: Number,
    required: true,
  },
  type: {
    type: String,
    default: "active",
  },
});

// Сортировка
const sortBy = ref("newest");
const sortOptions = [
  { value: "newest", label: "Сначала новые" },
  { value: "oldest", label: "Сначала старые" },
  { value: "price_asc", label: "Сначала дешевле" },
  { value: "price_desc", label: "Сначала дороже" },
];

const sortedListings = computed(() => {
  let sorted = [...props.listings];

  switch (sortBy.value) {
    case "newest":
      return sorted.sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
      );
    case "oldest":
      return sorted.sort(
        (a, b) => new Date(a.created_at) - new Date(b.created_at)
      );
    case "price_asc":
      return sorted.sort((a, b) => (a.price || 0) - (b.price || 0));
    case "price_desc":
      return sorted.sort((a, b) => (b.price || 0) - (a.price || 0));
    default:
      return sorted;
  }
});
</script>

<template>
  <div>
    <!-- Сортировка (только если есть объявления) -->
    <div v-if="listings.length > 0" class="flex justify-end mb-4">
      <select
        v-model="sortBy"
        class="px-3 py-1 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option
          v-for="option in sortOptions"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
    </div>

    <!-- Сетка объявлений -->
    <div
      v-if="listings.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <Card
        v-for="listing in sortedListings"
        :key="listing.id"
        :listing="listing"
        :type="listing.listing_type"
      />
    </div>

    <!-- Пустое состояние -->
    <div v-else class="text-center py-12">
      <i class="fa-regular fa-inbox text-6xl text-gray-400 mb-4"></i>
      <p class="text-gray-500 dark:text-gray-400">
        {{
          type === "active"
            ? "Нет активных объявлений"
            : "Нет завершенных объявлений"
        }}
      </p>
      <Link
        v-if="type === 'active'"
        :href="route('listing.create')"
        class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
      >
        Создать объявление
      </Link>
    </div>
  </div>
</template>