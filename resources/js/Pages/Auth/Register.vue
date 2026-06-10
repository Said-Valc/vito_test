<script setup>
import Container from "../../Components/Container.vue";
import Title from "../../Components/Title.vue";
import TextLink from "../../Components/TextLink.vue";
import InputField from "../../Components/InputField.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import ErrorMessages from "../../Components/ErrorMessages.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.post(route("register"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>
<template>
  <Head title="- Register" />
  <Container class="w-1/2">
    <div class="mb-8 text-center">
      <Title>Зарегистрируйте новый аккаунт</Title>
      <p>
        У вас уже есть аккаунт?
        <TextLink routeName="login" label="Авторизация" />
      </p>
    </div>

    <!-- Error messages -->
    <ErrorMessages :errors="form.errors" />
    <form @submit.prevent="submit" class="space-y-6">
      <InputField label="Имя" icon="id-badge" v-model="form.name" />
      <InputField label="Email" icon="at" v-model="form.email" />
      <InputField
        label="Пароль"
        type="password"
        icon="key"
        v-model="form.password"
      />
      <InputField
        label="Повторить пароль"
        type="password"
        icon="key"
        v-model="form.password_confirmation"
      />

      <p class="text-slate-500 text-sm dark:text-slate-400">
        Создавая учетную запись, вы соглашаетесь с нашими Условиями обслуживания
        и Политикой конфиденциальности.
      </p>

      <PrimaryBtn :disabled="form.processing">Регистрация</PrimaryBtn>
    </form>
  </Container>
</template>