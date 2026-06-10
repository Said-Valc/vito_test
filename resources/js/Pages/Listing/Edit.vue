<!-- resources/js/Pages/Listing/Edit.vue -->
<script setup>
import Container from "../../Components/Container.vue";
import Title from "../../Components/Title.vue";
import InputField from "../../Components/InputField.vue";
import TextArea from "../../Components/TextArea.vue";
import ImageUpload from "../../Components/ImageUpload.vue";
import ErrorMessages from "../../Components/ErrorMessages.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import ImageSlider from "../../Components/ImageSlider.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  listing: Object,
});

const form = useForm({
  title: props.listing.title,
  desc: props.listing.desc,
  tags: props.listing.tags,
  email: props.listing.email,
  link: props.listing.link,
  images: [], // Для новых изображений
  deleted_images: [], // ID изображений для удаления
  _method: "PUT",
});

// Существующие изображения
const existingImages = ref(props.listing.images || []);

// Удаление существующего изображения
const removeExistingImage = (imageId) => {
  if (confirm("Удалить это изображение?")) {
    // Добавляем ID в список на удаление
    form.deleted_images.push(imageId);
    // Удаляем из отображаемого списка
    existingImages.value = existingImages.value.filter(
      (img) => img.id !== imageId
    );
  }
};

// Обработка новых изображений
const handleImageUpload = (files) => {
  form.images = files;
};

// Восстановление удаленного изображения (отмена удаления)
const restoreImage = (image) => {
  existingImages.value.push(image);
  form.deleted_images = form.deleted_images.filter((id) => id !== image.id);
};
</script>

<template>
  <Head title="- Edit Listing" />

  <Container>
    <div class="mb-6">
      <Title>Редактирование объявления</Title>
    </div>

    <ErrorMessages :errors="form.errors" />

    <form
      @submit.prevent="form.post(route('listing.update', listing.id))"
      class="grid grid-cols-2 gap-6"
    >
      <div class="space-y-6">
        <InputField
          label="Название"
          icon="heading"
          placeholder="My new listing"
          v-model="form.title"
        />

        <InputField
          label="Теги (разделяются запятой)"
          icon="tags"
          placeholder="one, two, three"
          v-model="form.tags"
        />

        <TextArea
          label="Описание"
          icon="newspaper"
          placeholder="This is my listing description"
          v-model="form.desc"
        />
      </div>

      <div class="space-y-6">
        <InputField
          label="Email"
          icon="at"
          placeholder="example@email.com"
          v-model="form.email"
        />

        <InputField
          label="Внешняя ссылка"
          icon="up-right-from-square"
          placeholder="https://example.com"
          v-model="form.link"
        />

        <!-- Существующие изображения -->
        <div v-if="existingImages.length > 0" class="space-y-2">
          <label class="block text-sm font-medium">Текущие изображения</label>
          <div class="grid grid-cols-3 gap-2">
            <div
              v-for="image in existingImages"
              :key="image.id"
              class="relative group"
            >
              <img
                :src="`/storage/${image.path}`"
                class="w-full h-24 object-cover rounded-lg"
                alt=""
              />
              <!-- Кнопка удаления -->
              <button
                type="button"
                @click="removeExistingImage(image.id)"
                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <i class="fa-solid fa-times text-xs"></i>
              </button>
            </div>
          </div>
          <p class="text-xs text-slate-500">
            Нажмите на изображение, чтобы удалить
          </p>
        </div>

        <!-- Загрузка новых изображений -->
        <ImageUpload
          @image="handleImageUpload"
          :multiple="true"
          :existing-images="existingImages"
        />

        <!-- Превью новых изображений -->
        <div v-if="form.images.length > 0" class="space-y-2">
          <label class="block text-sm font-medium">Новые изображения</label>
          <div class="grid grid-cols-3 gap-2">
            <div
              v-for="(file, index) in form.images"
              :key="index"
              class="relative"
            >
              <img
                :src="URL.createObjectURL(file)"
                class="w-full h-24 object-cover rounded-lg"
                alt=""
              />
              <button
                type="button"
                @click="form.images.splice(index, 1)"
                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
              >
                <i class="fa-solid fa-times text-xs"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-span-2 flex gap-4">
        <PrimaryBtn :disabled="form.processing">
          {{ form.processing ? "Сохранение..." : "Сохранить изменения" }}
        </PrimaryBtn>

        <button
          type="button"
          @click="form.reset()"
          class="px-4 py-2 bg-slate-300 rounded-md hover:bg-slate-400"
        >
          Сбросить
        </button>
      </div>
    </form>
  </Container>
</template>