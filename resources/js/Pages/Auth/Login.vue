<script setup>
import Container from "../../Components/Container.vue";
import Title from "../../Components/Title.vue";
import TextLink from "../../Components/TextLink.vue";
import InputField from "../../Components/InputField.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import ErrorMessages from "../../Components/ErrorMessages.vue";
import SessionMessages from "../../Components/SessionMessages.vue";
import CheckBox from "../../Components/CheckBox.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
  email: "",
  password: "",
  remember: null,
});

defineProps({
  status: String,
});

const submit = () => {
  form.post(route("login"), {
    onFinish: () => form.reset("password"),
  });
};
</script>
<template>
  <Head title="- Login" />
  <Container class="w-1/2">
    <div class="mb-8 text-center">
      <Title>Войдите в свой аккаунт</Title>
      <p>
        Нужен аккаунт?
        <TextLink routeName="register" label="Регистрация" />
      </p>
    </div>

    <!-- Error messages -->
    <ErrorMessages :errors="form.errors" />
    <SessionMessages :status="status" />
    <form @submit.prevent="submit" class="space-y-6">
      <InputField label="Email" icon="at" v-model="form.email" />
      <InputField
        label="Пароль"
        type="password"
        icon="key"
        v-model="form.password"
      />

      <div class="flex items-center justify-between">
        <CheckBox name="remember" v-model="form.remember"
          >Запомнить меня</CheckBox
        >

        <TextLink routeName="password.request" label="Восстановить пароль" />
      </div>

      <PrimaryBtn :disabled="form.processing">Авторизоваться</PrimaryBtn>
    </form>
  </Container>
</template>