<script setup>
import { router, useForm } from "@inertiajs/vue3";
import Title from "../../Components/Title.vue";
import InputField from "../../Components/InputField.vue";
import PaginationLinks from "../../Components/PaginationLinks.vue";
import SessionMessages from "../../Components/SessionMessages.vue";

const props = defineProps({
  user: Object,
  listings: Object,
  status: String,
});

const params = route().params;
const form = useForm({ search: params.search });

const search = () => {
  router.get(
    route("user.show", {
      user: props.user.id,
      search: form.search,
      disapproved: params.disapproved,
    })
  );
};

const showDisapproved = (e) => {
  if (e.target.checked) {
    router.get(
      route("user.show", {
        user: props.user.id,
        search: params.search,
        disapproved: true,
      })
    );
  } else {
    router.get(
      route("user.show", {
        user: props.user.id,
        search: params.search,
        disapproved: null,
      })
    );
  }
};

const toggleApprove = (listing) => {
  let msg = listing.approved
    ? "Отклонить это объявление?"
    : "Одобрить это объявление?";

  if (confirm(msg)) {
    router.put(route("admin.approve", listing.id));
  }
};
</script>

<template>
  <Head :title="`- ${user.name} Listings`" />

  <SessionMessages :status="status" />
  <!-- Heading -->
  <div class="mb-6">
    <Title>{{ user.name }} Последние объявления</Title>
    <div class="flex items-end justify-between">
      <div class="flex items-end gap-2">
        <!-- Search form -->
        <form @submit.prevent="search">
          <InputField
            label=""
            icon="magnifying-glass"
            placeholder="Поиск..."
            v-model="form.search"
          />
        </form>
        <Link
          class="px-2 py-[6px] rounded-md bg-blue-500 text-white flex items-center gap-2"
          v-if="params.search"
          :href="
            route('user.show', {
              ...params,
              search: null,
              page: null,
              user: user.id,
            })
          "
        >
          {{ params.search }}
          <i class="fa-solid fa-xmark"></i>
        </Link>
      </div>

      <!-- Toggle approve listing btn -->
      <div
        class="flex items-center gap-1 text-xs hover:bg-blue-50 px-2 py-1 rounded-md"
      >
        <input
          @input="showDisapproved"
          :checked="params.disapproved"
          type="checkbox"
          id="showDisapproved"
          class="rounded-md border-1 outline-0 text-blue-500 ring-blue-400 border-slate-300 cursor-pointer"
        />
        <label
          for="showDisapproved"
          class="block text-sm font-medium text-slate-700 cursor-pointer"
        >
          Показать отклоненные объявления
        </label>
      </div>
    </div>
  </div>

  <!-- Table -->
  <table
    class="bg-white w-full rounded-lg overflow-hidden ring-1 ring-blue-200"
  >
    <thead>
      <tr class="bg-blue-500 text-white uppercase text-xs text-left">
        <th class="w-4/6 p-3">Название</th>
        <th class="w-2/6 p-3 text-center">Подтвержден</th>
        <th class="w-1/6 p-3 text-right">Посмотреть</th>
      </tr>
    </thead>

    <tbody class="divide-y divide-blue-100 divide-dashed">
      <tr v-for="listing in listings.data" :key="listing.id">
        <td class="py-5 px-3">{{ listing.title }}</td>

        <td class="py-5 px-3 text-2xl text-center">
          <button @click.prevent="toggleApprove(listing)">
            <i
              :class="`fa-solid fa-${
                listing.approved
                  ? 'circle-check text-green-500'
                  : 'circle-xmark text-red-500'
              }`"
            ></i>
          </button>
        </td>

        <td class="w-1/6 py-5 px-3 text-right">
          <Link
            :href="route('listing.show', listing.id)"
            class="fa-solid fa-up-right-from-square px-3 text-blue-500"
          ></Link>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="mt-6">
    <PaginationLinks :paginator="listings" />
  </div>
</template>