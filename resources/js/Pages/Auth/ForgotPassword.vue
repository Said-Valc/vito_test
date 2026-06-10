<script setup>
import Container from "../../Components/Container.vue";
import InputField from "../../Components/InputField.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import ErrorMessages from "../../Components/ErrorMessages.vue";
import SessionMessages from "../../Components/SessionMessages.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
  email: "",
});

defineProps({ status: String });

const submit = () => {
  form.post(route("password.email"));
};
</script>

<template>
  <Head title="- Forgot Password" />
  <Container class="w-1/2">
    <div class="mb-8 text-center">
      <p>
        Забыли пароль? Не проблема. Просто сообщите нам свой адрес электронной
        почты, и мы вышлем вам ссылку для сброса пароля, которая позволит вам
        выбрать новый.
      </p>
    </div>

    <!-- Errors messages -->
    <ErrorMessages :errors="form.errors" />

    <SessionMessages :status="status" />

    <form @submit.prevent="submit" class="space-y-6">
      <InputField label="Email" icon="at" v-model="form.email" />

      <PrimaryBtn :disabled="form.processing">
        Отправить ссылку для сброса пароля
      </PrimaryBtn>
    </form>
  </Container>
</template>