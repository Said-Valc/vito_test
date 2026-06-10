<script setup>
import PaginationLinks from "../../Components/PaginationLinks.vue";
import RoleSelect from "../../Components/RoleSelect.vue";
import SessionMessages from "../../Components/SessionMessages.vue";
import InputField from "../../Components/InputField.vue";
import { router, useForm } from "@inertiajs/vue3";

defineProps({ users: Object, status: String });

const params = route().params;
const form = useForm({ search: params.search });

const search = () => {
  router.get(route("admin.index"), {
    search: form.search,
    user_role: params.user_role,
  });
};

const toggleRole = (e) => {
  if (e.target.checked) {
    router.get(
      route("admin.index", {
        search: params.search,
        user_role: "suspended",
      })
    );
  } else {
    router.get(
      route("admin.index", {
        search: params.search,
        user_role: null,
      })
    );
  }
};
</script>

<template>
  <Head title="- Admin" />

  <SessionMessages :status="status" />

  <!-- Heading -->
  <div class="flex items-end justify-between mb-4">
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
          route('admin.index', {
            ...params,
            search: null,
            page: null,
          })
        "
      >
        {{ params.search }}
        <i class="fa-solid fa-xmark"></i>
      </Link>
    </div>

    <!-- Toggle role btn -->
    <div
      class="flex items-center gap-1 text-xs hover:bg-blue-50 px-2 py-1 rounded-md"
    >
      <input
        @input="toggleRole"
        :checked="params.user_role"
        type="checkbox"
        id="toggleRole"
        class="rounded-md border-1 outline-0 text-blue-500 ring-blue-400 border-slate-300 cursor-pointer"
      />
      <label
        for="toggleRole"
        class="block text-sm font-medium text-slate-700 cursor-pointer"
      >
        Показать заблокированных пользователей
      </label>
    </div>
  </div>

  <!-- Table -->
  <table
    class="bg-white w-full rounded-lg overflow-hidden ring-1 ring-blue-200"
  >
    <thead>
      <tr class="bg-blue-500 text-white uppercase text-xs text-left">
        <th class="w-3/6 p-3">Имя</th>
        <th class="w-2/6 p-3">Роль</th>
        <th class="w-1/6 p-3">объявлении</th>
        <th class="w-1/6 p-3 text-right">Посмотреть</th>
      </tr>
    </thead>

    <tbody class="divide-y divide-blue-100 divide-dashed">
      <tr v-for="user in users.data" :key="user.id">
        <td class="w-3/6 py-5 px-3">
          <p class="font-bold mb-1">{{ user.name }}</p>
          <p class="font-light text-xs">{{ user.email }}</p>
        </td>

        <td class="w-2/6 py-5 px-3">
          <RoleSelect :user="user" />
        </td>

        <td class="w-1/6 py-5 px-3">
          <div class="flex items-center gap-6">
            <div class="flex items-center gap-1">
              <p>
                {{ user.listings.filter((l) => l.approved).length }}
              </p>
              <i class="fa-solid fa-circle-check text-green-500"></i>
            </div>
            <div class="flex items-center gap-1">
              <p>
                {{ user.listings.filter((l) => !l.approved).length }}
              </p>
              <i class="fa-solid fa-circle-xmark text-red-500"></i>
            </div>
          </div>
        </td>

        <td class="w-1/6 py-5 px-3 text-right">
          <Link
            :href="route('user.show', user.id)"
            class="fa-solid fa-up-right-from-square px-3 text-blue-500"
          ></Link>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="mt-6">
    <PaginationLinks :paginator="users" />
  </div>
</template>