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
  password: "",
});

const submit = () => {
  form.post(route("password.confirm"), {
    onFinish: () => form.reset(),
  });
};
</script>
<template>
  <Head title="- Password Confirmation" />
  <Container class="w-1/2">
    <div class="mb-8 text-center">
      <p>
        Это защищенная область приложения. Пожалуйста, подтвердите свой пароль,
        прежде чем продолжить.
      </p>
    </div>

    <ErrorMessages :errors="form.errors" />
    <form @submit.prevent="submit" class="space-y-6">
      <InputField
        label="Password"
        type="password"
        icon="key"
        v-model="form.password"
      />

      <PrimaryBtn :disabled="form.processing">Подтвердить</PrimaryBtn>
    </form>
  </Container>
</template>