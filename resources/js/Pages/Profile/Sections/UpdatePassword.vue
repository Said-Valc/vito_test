<script setup>
import Container from "../../../Components/Container.vue";
import Title from "../../../Components/Title.vue";
import InputField from "../../../Components/InputField.vue";
import PrimaryBtn from "../../../Components/PrimaryBtn.vue";
import ErrorMessages from "../../../Components/ErrorMessages.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.put(route("profile.password"), {
    onSuccess: () => form.reset(),
    preserveScroll: true,
  });
};
</script>

<template>
  <Container class="mb-6">
    <div class="mb-6">
      <Title>Редактирование Пароля</Title>
      <p class="text-slate-600">
        Для обеспечения безопасности используйте длинный, случайный пароль.
      </p>
    </div>

    <ErrorMessages :errors="form.errors" />

    <form @submit.prevent="submit" class="space-y-6">
      <InputField
        label="Текущий пароль"
        icon="key"
        class="w-full md:w-1/2"
        type="password"
        v-model="form.current_password"
      />

      <InputField
        label="Новый пароль"
        icon="key"
        class="w-full md:w-1/2"
        type="password"
        v-model="form.password"
      />

      <InputField
        label="Подтвердите новый пароль"
        icon="key"
        class="w-full md:w-1/2"
        type="password"
        v-model="form.password_confirmation"
      />

      <div v-if="form.recentlySuccessful" class="text-green-500 font-medium">
        Пароль изменён!
      </div>

      <PrimaryBtn :disabled="form.processing">Сохранить</PrimaryBtn>
    </form>
  </Container>
</template>