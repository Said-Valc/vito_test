<script setup>
import UpdateInfo from "./Sections/UpdateInfo.vue";
import UpdatePassword from "./Sections/UpdatePassword.vue";
import DeleteAccount from "./Sections/DeleteAccount.vue";

defineProps({
  user: Object,
  status: String,
});
</script>

<template>
  <Head title="- Profile" />

  <div class="py-6">
    <div
      class="bg-white rounded-lg shadow border-2 border-blue-100 overflow-hidden p-6"
    >
      <h1 class="text-2xl font-bold text-slate-800 mb-6">Настройки профиля</h1>

      <div class="max-w-3xl space-y-6">
        <UpdateInfo :user="user" :status="status" />
        <UpdatePassword />
        <DeleteAccount />
      </div>
    </div>
  </div>
</template>