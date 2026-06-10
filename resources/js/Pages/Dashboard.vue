<script setup>
import Title from "../Components/Title.vue";
import PaginationLinks from "../Components/PaginationLinks.vue";
import SessionMessages from "../Components/SessionMessages.vue";
import { router, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";

defineProps({
  listings: Object,
  status: String,
});

const sliderRefs = ref({});

// Получаем URL первого изображения
const getFirstImageUrl = (listing) => {
  if (listing.images_urls && listing.images_urls.length > 0) {
    return listing.images_urls[0];
  }
  return "/images/no-image.jpg";
};

// Удаление объявления
const deleteListing = (listing) => {
  if (confirm("Вы уверены?")) {
    router.delete(
      route("listing.destroy", {
        type: listing.listing_type || "auto",
        id: listing.id,
      })
    );
  }
};
</script>

<template>
  <Head title="- Страница" />

  <SessionMessages :status="status" />

  <div v-if="listings">
    <div v-if="listings.data && Object.keys(listings.data).length">
      <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
          <Title>Все добавленные объявления</Title>

          <div class="flex items-center gap-4 text-xs">
            <p>
              Активные
              <i class="fa-solid fa-circle-check text-green-500"></i>
            </p>
            <p>
              Не активные
              <i class="fa-solid fa-circle-xmark text-red-500"></i>
            </p>
          </div>
        </div>

        <table
          class="w-full table-fixed border-collapse overflow-hidden rounded-md text-sm ring-1 ring-blue-200 bg-white shadow-lg"
        >
          <thead class="bg-blue-500 text-xs uppercase text-white">
            <tr>
              <th class="w-3/4 p-3 text-left">Название</th>
              <th class="w-1/4 py-3 pr-3 text-right">Подробно</th>
              <th class="w-1/5 py-3 pr-3 text-right">Править</th>
              <th class="w-1/5 py-3 pr-3 text-right">Удалить</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="listing in listings.data"
              :key="listing.id"
              class="border-b border-blue-100 hover:bg-blue-50"
            >
              <td class="w-3/4 p-3 text-left">
                <div class="flex items-center gap-2">
                  <!-- Заглушка изображения -->
                  <img
                    :src="getFirstImageUrl(listing)"
                    class="w-16 h-16 rounded object-cover"
                    @error="(e) => (e.target.src = '/images/no-image.jpg')"
                  />

                  <h4 class="font-bold">
                    {{ listing.title }}
                    <i
                      :class="`fa-solid fa-${
                        listing.approved
                          ? 'circle-check text-green-500'
                          : 'circle-xmark text-red-500'
                      }`"
                    ></i>
                  </h4>
                </div>
              </td>

              <td class="w-1/4 py-3 pr-3 text-right text-blue-500">
                <Link
                  v-if="listing.approved"
                  :href="
                    route('listing.show', {
                      type: listing.listing_type || 'auto',
                      id: listing.id,
                    })
                  "
                >
                  Посмотреть
                </Link>
              </td>

              <td class="w-1/5 py-3 pr-3 text-right text-blue-500">
                <Link
                  :href="
                    route('listing.edit', {
                      type: listing.listing_type || 'auto',
                      id: listing.id,
                    })
                  "
                >
                  Редактировать
                </Link>
              </td>

              <td class="w-1/5 py-3 pr-3 text-right text-red-500">
                <button type="button" @click="deleteListing(listing)">
                  Удалить
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div>
        <PaginationLinks :paginator="listings" />
      </div>
    </div>

    <div v-else>Все добавленные объявления отсутствуют!</div>
  </div>

  <div v-else>
    В связи с нарушением наших условий ваша учетная запись заблокирована.
    Пожалуйста, свяжитесь с нами по адресу:
    <span class="text-link">email@admin.com</span>
  </div>
</template>