<script setup>
import Container from "../../Components/Container.vue";
import Title from "../../Components/Title.vue";
import InputField from "../../Components/InputField.vue";
import TextArea from "../../Components/TextArea.vue";
import SelectField from "../../Components/SelectField.vue";
import ImageUpload from "../../Components/ImageUpload.vue";
import ErrorMessages from "../../Components/ErrorMessages.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import LocationPicker from "@/Components/LocationPicker.vue";
import { useForm, Head, Link } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted } from "vue";
import AutocompleteInput from "@/Components/AutocompleteInput.vue";
import ColorPicker from "@/Components/ColorPicker.vue";
import { carBrands } from "@/data/carBrands";
import { carModels } from "@/data/carModels";
import { carGenerations } from "@/data/carGenerations";
import { carColors } from "@/data/carColors";

const props = defineProps({
  categories: {
    type: Object,
    default: () => ({}),
  },
});

const form = useForm({
  // Основные поля
  category_type: null,
  category_id: null,
  title: null,
  description: null,
  price: null,
  city: null,
  phone: null,
  images: [],

  // Поля геолокации
  location_id: null,
  latitude: null,
  longitude: null,

  // Поля для авто
  brand: null,
  model: null,
  generation: null,
  year: null,
  mileage: null,
  fuel_type: null,
  transmission: null,
  color: null,

  // Поля для недвижимости
  action: null,
  rooms: null,
  area_total: null,
  floor: null,
  floors_total: null,
  address: null,

  // Поля для электроники
  condition: null,

  // Поля для работы
  job_type: null,
  company_name: null,
  employment_type: null,
  work_schedule: null,
  full_name: null,
  age: null,
  specialization: null,
  salary_from: null,
  salary_to: null,
  salary_expectation: null,

  // Поля для услуг
  service_type: null,
  price_type: null,
});

const subcategories = ref([]);
const showSubcategorySelect = ref(false);
const isSubcategoryRequired = ref(false);
const selectedLocation = ref(null);

// Refs для полей авто
const brandAutocompleteRef = ref(null);
const modelAutocompleteRef = ref(null);
const generationAutocompleteRef = ref(null);

// Флаги выбора из списка
const isBrandSelectedFromList = ref(false);
const isModelSelectedFromList = ref(false);

// Основные категории
const mainCategories = [
  { value: "auto", label: "Авто" },
  { value: "nedvizhimost", label: "Недвижимость" },
  { value: "elektronika", label: "Электроника" },
  { value: "hobby", label: "Хобби и отдых" },
  { value: "rabota", label: "Работа" },
  { value: "uslugi", label: "Услуги" },
  { value: "lichnie_veschi", label: "Личные вещи" },
  { value: "dlya_doma", label: "Для дома" },
];

// Категории, для которых нужна подкатегория
const categoriesWithRequiredSubcategory = [
  "auto",
  "nedvizhimost",
  "elektronika",
  "rabota",
];

// Доступные модели только если марка выбрана ИЗ СПИСКА
const availableModels = computed(() => {
  if (!form.brand || !isBrandSelectedFromList.value) return [];
  return carModels[form.brand] || [];
});

// Доступные поколения только если модель выбрана ИЗ СПИСКА
const availableGenerations = computed(() => {
  if (!form.brand || !isBrandSelectedFromList.value) return [];
  if (!form.model || !isModelSelectedFromList.value) return [];
  const brandGenerations = carGenerations[form.brand];
  if (!brandGenerations) return [];
  return brandGenerations[form.model] || [];
});

// Обработчик выбора марки ИЗ СПИСКА
const handleBrandSelectedFromList = (brand) => {
  if (brand) {
    isBrandSelectedFromList.value = true;
    isModelSelectedFromList.value = false;
    form.brand = brand;
    form.model = "";
    form.generation = "";

    setTimeout(() => {
      if (modelAutocompleteRef.value) {
        modelAutocompleteRef.value.focus();
      }
    }, 200);
  }
};

// Обработчик выбора модели ИЗ СПИСКА
const handleModelSelectedFromList = (model) => {
  if (model) {
    isModelSelectedFromList.value = true;
    form.model = model;
    form.generation = "";

    // Если есть поколения - фокусируем поле
    const brandGenerations = carGenerations[form.brand];
    if (
      brandGenerations &&
      brandGenerations[model] &&
      brandGenerations[model].length > 0
    ) {
      setTimeout(() => {
        if (generationAutocompleteRef.value) {
          generationAutocompleteRef.value.focus();
        }
      }, 200);
    }
  }
};

// Следим за изменением марки
watch(
  () => form.brand,
  (newVal) => {
    if (!newVal) {
      isBrandSelectedFromList.value = false;
      isModelSelectedFromList.value = false;
      form.model = "";
      form.generation = "";
    }
  }
);

// Следим за изменением модели
watch(
  () => form.model,
  (newVal) => {
    if (!newVal) {
      isModelSelectedFromList.value = false;
      form.generation = "";
    }
  }
);

// Обработчик выбора локации
const handleLocationSelected = (location) => {
  selectedLocation.value = location;

  if (location) {
    form.city = location.name;
    form.location_id = location.id;
    form.latitude = location.latitude;
    form.longitude = location.longitude;
  } else {
    form.city = "";
    form.location_id = null;
    form.latitude = null;
    form.longitude = null;
  }
};

// Обработчик выбора подкатегории
const handleSubcategorySelected = (categoryId) => {
  form.category_id = categoryId;

  if (!categoryId) return;

  const selectedSubcategory = subcategories.value.find(
    (s) => s.id == categoryId
  );
  if (!selectedSubcategory) return;

  if (form.category_type === "nedvizhimost" && selectedSubcategory.action) {
    form.action = selectedSubcategory.action;
  }

  if (form.category_type === "rabota" && selectedSubcategory.type) {
    form.job_type = selectedSubcategory.type;
  }
};

// Загрузка подкатегорий при выборе основной категории
watch(
  () => form.category_type,
  (newType) => {
    form.category_id = null;
    form.action = null;
    form.job_type = null;

    isSubcategoryRequired.value =
      categoriesWithRequiredSubcategory.includes(newType);

    if (
      isSubcategoryRequired.value &&
      newType &&
      props.categories &&
      props.categories[newType]
    ) {
      const subcats = props.categories[newType];

      if (subcats && Array.isArray(subcats) && subcats.length > 0) {
        subcategories.value = subcats;
        showSubcategorySelect.value = true;
      } else {
        subcategories.value = [];
        showSubcategorySelect.value = false;
        form.category_id = -1;
      }
    } else {
      subcategories.value = [];
      showSubcategorySelect.value = false;
      form.category_id = null;
    }
  },
  { immediate: true }
);

// Динамические поля в зависимости от категории
const dynamicFields = computed(() => {
  switch (form.category_type) {
    case "auto":
      return [
        {
          type: "autocomplete",
          label: "Марка",
          model: "brand",
          icon: "car",
          required: true,
          placeholder: "Начните вводить марку...",
          items: carBrands,
          itemsLabel: "марок",
          onItemSelected: handleBrandSelectedFromList,
        },
        {
          type: "autocomplete",
          label: "Модель",
          model: "model",
          icon: "car-side",
          required: true,
          placeholder:
            form.brand && isBrandSelectedFromList.value
              ? `Модели ${form.brand}...`
              : "Сначала выберите марку из списка",
          items: availableModels.value,
          itemsLabel: "моделей",
          disabled: !form.brand || !isBrandSelectedFromList.value,
          onItemSelected: handleModelSelectedFromList,
        },
        {
          type: "autocomplete",
          label: "Поколение",
          model: "generation",
          icon: "code-branch",
          required: false,
          placeholder:
            form.model &&
            isModelSelectedFromList.value &&
            availableGenerations.value.length > 0
              ? `Поколения ${form.brand} ${form.model}...`
              : availableGenerations.value.length === 0 &&
                form.model &&
                isModelSelectedFromList.value
              ? "Нет данных о поколениях (можно ввести своё)"
              : "Сначала выберите модель из списка",
          items: availableGenerations.value,
          itemsLabel: "поколений",
          disabled:
            !form.model ||
            !isModelSelectedFromList.value ||
            availableGenerations.value.length === 0,
        },
        {
          type: "input",
          label: "Год",
          model: "year",
          icon: "calendar",
          inputType: "number",
          required: true,
          placeholder: "2020",
        },
        {
          type: "input",
          label: "Пробег (км)",
          model: "mileage",
          icon: "road",
          inputType: "number",
          placeholder: "50000",
        },
        {
          type: "select",
          label: "Топливо",
          model: "fuel_type",
          icon: "gas-pump",
          options: ["Бензин", "Дизель", "Электро", "Гибрид", "Газ"],
        },
        {
          type: "select",
          label: "КПП",
          model: "transmission",
          icon: "gear",
          options: ["Механика", "Автомат", "Робот", "Вариатор"],
        },
        {
          type: "colorpicker",
          label: "Цвет",
          model: "color",
          required: false,
          placeholder: "Начните вводить цвет...",
          items: carColors,
          itemsLabel: "цветов",
        },
      ];

    case "nedvizhimost":
      return [
        {
          type: "input",
          label: "Комнат",
          model: "rooms",
          icon: "door-open",
          inputType: "number",
          placeholder: "3",
        },
        {
          type: "input",
          label: "Площадь (м²)",
          model: "area_total",
          icon: "ruler",
          inputType: "number",
          placeholder: "65.5",
        },
        {
          type: "input",
          label: "Этаж",
          model: "floor",
          icon: "arrow-up",
          inputType: "number",
          placeholder: "5",
        },
        {
          type: "input",
          label: "Этажей в доме",
          model: "floors_total",
          icon: "building",
          inputType: "number",
          placeholder: "9",
        },
        {
          type: "input",
          label: "Адрес",
          model: "address",
          icon: "location-dot",
          required: true,
          placeholder: "ул. Ленина, д. 10",
        },
      ];

    case "elektronika":
      return [
        {
          type: "input",
          label: "Бренд",
          model: "brand",
          icon: "tag",
          required: true,
          placeholder: "Apple",
        },
        {
          type: "input",
          label: "Модель",
          model: "model",
          icon: "laptop",
          required: true,
          placeholder: "iPhone 15 Pro",
        },
        {
          type: "select",
          label: "Состояние",
          model: "condition",
          icon: "star",
          options: [
            "Новый",
            "Как новый",
            "Хорошее",
            "Среднее",
            "Требует ремонта",
          ],
          required: true,
        },
      ];

    case "uslugi":
      return [
        {
          type: "input",
          label: "Тип услуги",
          model: "service_type",
          icon: "hand-sparkles",
          required: true,
          placeholder: "Ремонт, Клининг, Перевозки и т.д.",
        },
        {
          type: "select",
          label: "Тип цены",
          model: "price_type",
          icon: "money-bill",
          options: ["За час", "За услугу", "За проект", "Договорная"],
        },
      ];

    case "rabota":
      return [];

    default:
      return [];
  }
});

// Дополнительные поля для работы
const workFields = computed(() => {
  if (form.category_type !== "rabota") return [];
  if (!form.job_type) return [];

  if (form.job_type === "vacancy") {
    return [
      {
        type: "input",
        label: "Компания",
        model: "company_name",
        icon: "building",
        required: true,
        placeholder: 'ООО "Технологии"',
      },
      {
        type: "select",
        label: "Тип занятости",
        model: "employment_type",
        icon: "briefcase",
        options: ["Полная", "Частичная", "Стажировка", "Проектная работа"],
        required: true,
      },
      {
        type: "select",
        label: "График работы",
        model: "work_schedule",
        icon: "clock",
        options: ["Полный день", "Сменный", "Гибкий", "Вахтовый", "Удаленно"],
        required: true,
      },
      {
        type: "input",
        label: "Зарплата от",
        model: "salary_from",
        icon: "money-bill",
        inputType: "number",
        placeholder: "50000",
      },
      {
        type: "input",
        label: "Зарплата до",
        model: "salary_to",
        icon: "money-bill",
        inputType: "number",
        placeholder: "100000",
      },
    ];
  } else if (form.job_type === "resume") {
    return [
      {
        type: "input",
        label: "ФИО",
        model: "full_name",
        icon: "user",
        required: true,
        placeholder: "Иванов Иван Иванович",
      },
      {
        type: "input",
        label: "Возраст",
        model: "age",
        icon: "calendar",
        inputType: "number",
        placeholder: "30",
      },
      {
        type: "input",
        label: "Специализация",
        model: "specialization",
        icon: "briefcase",
        placeholder: "Программист",
      },
      {
        type: "input",
        label: "Ожидаемая зарплата",
        model: "salary_expectation",
        icon: "money-bill",
        inputType: "number",
        placeholder: "80000",
      },
    ];
  }

  return [];
});

// Отправка формы
const submitForm = () => {
  if (
    isSubcategoryRequired.value &&
    !form.category_id &&
    form.category_id !== -1
  ) {
    alert("Пожалуйста, выберите подкатегорию");
    return;
  }

  if (!selectedLocation.value) {
    alert("Пожалуйста, выберите местоположение");
    return;
  }

  const formData = new FormData();

  Object.keys(form).forEach((key) => {
    if (key === "images" && form.images.length > 0) {
      form.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image);
      });
    } else if (
      form[key] !== null &&
      form[key] !== undefined &&
      form[key] !== "" &&
      key !== "city"
    ) {
      formData.append(key, form[key]);
    }
  });

  form.post(route("listing.store"), {
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
    preserveScroll: true,
    onError: (errors) => {
      console.error("Ошибки валидации:", errors);
    },
    onSuccess: () => {
      selectedLocation.value = null;
    },
  });
};

onMounted(() => {
  console.log("Загруженные категории:", props.categories);
});
</script>

<template>
  <Head title="- Новое объявление" />

  <Container>
    <div class="mb-6">
      <Title>Добавление объявления</Title>
    </div>

    <ErrorMessages :errors="form.errors" />

    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Основная категория и подкатегория -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <SelectField
          label="Категория"
          icon="folder"
          v-model="form.category_type"
          :options="mainCategories"
          required
        />

        <SelectField
          v-if="showSubcategorySelect"
          label="Подкатегория"
          icon="chevron-right"
          v-model="form.category_id"
          :options="subcategories.map((s) => ({ value: s.id, label: s.name }))"
          :required="isSubcategoryRequired"
          @update:modelValue="handleSubcategorySelected"
        />
      </div>

      <!-- Общие поля -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <InputField
          label="Название объявления"
          icon="heading"
          placeholder="Например: Продам Toyota Camry"
          v-model="form.title"
          required
        />

        <InputField
          label="Цена"
          icon="money-bill"
          placeholder="0"
          type="number"
          v-model="form.price"
          required
        />

        <!-- Локация с автодополнением -->
        <div class="md:col-span-2">
          <label
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
          >
            <i class="fa-regular fa-location-dot mr-2"></i>
            Местоположение
          </label>
          <LocationPicker
            v-model="selectedLocation"
            @location-selected="handleLocationSelected"
            :error="form.errors.city || form.errors.location_id"
          />
          <p class="mt-1 text-xs text-slate-400">
            Укажите город, чтобы покупателям было проще найти ваше объявление.
            Мы автоматически определим ближайший город.
          </p>
        </div>

        <InputField
          label="Телефон"
          icon="phone"
          placeholder="+7 (999) 123-45-67"
          v-model="form.phone"
          required
        />
      </div>

      <!-- Информация о выбранной локации -->
      <div
        v-if="selectedLocation"
        class="bg-slate-50 rounded-lg p-4 border border-slate-200"
      >
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center"
          >
            <i class="fa-solid fa-check text-green-600"></i>
          </div>
          <div>
            <h4 class="font-medium text-slate-800">
              {{ selectedLocation.full_name }}
            </h4>
            <p class="text-sm text-slate-500">
              {{ selectedLocation.country || "Россия" }}
            </p>
          </div>
          <button
            type="button"
            @click="
              selectedLocation = null;
              handleLocationSelected(null);
            "
            class="ml-auto text-sm text-slate-400 hover:text-red-500 transition"
          >
            <i class="fa-regular fa-xmark mr-1"></i>
            Изменить
          </button>
        </div>
        <div
          v-if="selectedLocation.latitude && selectedLocation.longitude"
          class="mt-2 text-xs text-slate-400"
        >
          Координаты: {{ selectedLocation.latitude }},
          {{ selectedLocation.longitude }}
        </div>
      </div>

      <!-- Специфические поля для выбранной категории -->
      <div
        v-if="dynamicFields.length"
        class="grid grid-cols-1 md:grid-cols-2 gap-6"
      >
        <template v-for="field in dynamicFields" :key="field.model">
          <!-- ColorPicker -->
          <div v-if="field.type === 'colorpicker'">
            <label
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              <i class="fa-solid fa-palette mr-2"></i>
              {{ field.label }}
              <span v-if="field.required" class="text-red-500">*</span>
            </label>
            <ColorPicker
              v-model="form[field.model]"
              :items="field.items"
              :placeholder="field.placeholder"
              :required="field.required"
              :items-label="field.itemsLabel"
            />
          </div>

          <!-- Автодополнение -->
          <div v-else-if="field.type === 'autocomplete'">
            <label
              class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
            >
              <i :class="['fa-solid', field.icon, 'mr-2']"></i>
              {{ field.label }}
              <span v-if="field.required" class="text-red-500">*</span>
            </label>
            <AutocompleteInput
              v-model="form[field.model]"
              :items="field.items"
              :placeholder="field.placeholder"
              :icon="field.icon"
              :required="field.required"
              :items-label="field.itemsLabel"
              :disabled="field.disabled"
              @item-selected="
                field.onItemSelected ? field.onItemSelected($event) : undefined
              "
            />
          </div>

          <!-- Обычный select -->
          <SelectField
            v-else-if="field.type === 'select'"
            :label="field.label"
            :icon="field.icon"
            v-model="form[field.model]"
            :options="field.options"
            :required="field.required"
          />

          <!-- Обычный input -->
          <InputField
            v-else
            :label="field.label"
            :icon="field.icon"
            :type="field.inputType || 'text'"
            v-model="form[field.model]"
            :required="field.required"
            :placeholder="field.placeholder"
          />
        </template>
      </div>

      <!-- Дополнительные поля для работы -->
      <div
        v-if="workFields.length"
        class="grid grid-cols-1 md:grid-cols-2 gap-6"
      >
        <template v-for="field in workFields" :key="field.model">
          <SelectField
            v-if="field.type === 'select'"
            :label="field.label"
            :icon="field.icon"
            v-model="form[field.model]"
            :options="field.options"
            :required="field.required"
          />
          <InputField
            v-else
            :label="field.label"
            :icon="field.icon"
            :type="field.inputType || 'text'"
            v-model="form[field.model]"
            :required="field.required"
            :placeholder="field.placeholder"
          />
        </template>
      </div>

      <!-- Описание -->
      <TextArea
        label="Описание"
        icon="newspaper"
        placeholder="Подробное описание объявления..."
        v-model="form.description"
        rows="5"
        required
      />

      <!-- Загрузка изображений -->
      <div>
        <label
          class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
        >
          <i class="fa-regular fa-images mr-2"></i>
          Фотографии (можно несколько)
        </label>
        <ImageUpload
          :listingImages="form.images"
          @images="form.images = $event"
        />
        <p class="text-xs text-slate-400 mt-1">
          Поддерживаются форматы: JPG, PNG, GIF. Максимальный размер: 5MB
        </p>
      </div>

      <!-- Кнопка отправки -->
      <div class="flex justify-end">
        <PrimaryBtn :disabled="form.processing">
          <i
            v-if="form.processing"
            class="fa-regular fa-spinner fa-spin mr-2"
          ></i>
          {{ form.processing ? "Публикация..." : "Опубликовать объявление" }}
        </PrimaryBtn>
      </div>
    </form>
  </Container>
</template>