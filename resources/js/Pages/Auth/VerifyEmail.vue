<script setup>
import { useForm } from "@inertiajs/vue3";
import Container from "../../Components/Container.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import SessionMessages from "../../Components/SessionMessages.vue";

const form = useForm({});

defineProps({
  status: String,
});

const submit = () => {
  form.post(route("verification.send"));
};
</script>

<template>
  <Head title="- Email Verification" />

  <Container class="w-1/2">
    <div class="mb-8">
      <p>
        Спасибо за регистрацию! Прежде чем начать, пожалуйста, подтвердите свой
        адрес электронной почты, перейдя по ссылке, которую мы только что вам
        отправили. Если вы не получили письмо, мы с удовольствием вышлем вам еще
        одно.
      </p>
    </div>

    <SessionMessages :status="status" />

    <form @submit.prevent="submit">
      <PrimaryBtn :disabled="form.processing"
        >Повторная отправка письма с подтверждением.</PrimaryBtn
      >
    </form>
  </Container>
</template>