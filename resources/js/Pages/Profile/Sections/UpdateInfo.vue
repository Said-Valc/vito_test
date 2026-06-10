<script setup>
import Container from "../../../Components/Container.vue";
import Title from "../../../Components/Title.vue";
import InputField from "../../../Components/InputField.vue";
import PrimaryBtn from "../../../Components/PrimaryBtn.vue";
import ErrorMessages from "../../../Components/ErrorMessages.vue";
import SessionMessages from "../../../Components/SessionMessages.vue";
import { router, useForm } from "@inertiajs/vue3";

const props = defineProps({
  user: Object,
  status: String,
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
});

const resendEmail = (e) => {
  router.post(
    route("verification.send"),
    {},
    {
      onStart: () => (e.target.disabled = true),
      onFinish: () => (e.target.disabled = false),
    }
  );
};
</script>

<template>
  <Container class="mb-6">
    <div class="mb-6">
      <Title>Редактирование информации</Title>
      <p class="text-slate-600">
        Обновите информацию в профиле вашей учетной записи и адрес электронной
        почты.
      </p>
    </div>

    <ErrorMessages :errors="form.errors" />

    <form @submit.prevent="form.patch(route('profile.info'))" class="space-y-6">
      <InputField
        label="Имя"
        icon="id-badge"
        class="w-full md:w-1/2"
        v-model="form.name"
      />

      <InputField
        label="Email"
        icon="at"
        class="w-full md:w-1/2"
        v-model="form.email"
      />

      <div v-if="user.email_verified_at === null">
        <SessionMessages :status="status" />

        <p class="text-sm text-slate-600">
          Ваш адрес электронной почты не подтвержден.
          <button
            @click="resendEmail"
            class="text-blue-500 font-medium underline hover:text-blue-600 disabled:text-slate-400 disabled:cursor-wait"
          >
            Нажмите здесь, чтобы повторно отправить письмо с подтверждением.
          </button>
        </p>
      </div>

      <div v-if="form.recentlySuccessful" class="text-green-500 font-medium">
        Сохранено!
      </div>

      <PrimaryBtn :disabled="form.processing">Сохранить</PrimaryBtn>
    </form>
  </Container>
</template>