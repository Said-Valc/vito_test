<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Затемнение -->
        <div
          class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
          @click="close"
        ></div>

        <!-- Модальное окно -->
        <div
          class="relative bg-white rounded-lg shadow-xl border-2 border-blue-100 max-w-2xl w-full max-h-[90vh] overflow-hidden"
        >
          <!-- Заголовок -->
          <div
            class="sticky top-0 bg-white border-b border-blue-100 px-6 py-4 z-10"
          >
            <h3 class="text-xl font-semibold text-slate-800">
              Отзывы о продавце
            </h3>
            <button
              @click="close"
              class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition"
            >
              <i class="fa-regular fa-xmark text-2xl"></i>
            </button>
          </div>

          <div
            class="overflow-y-auto"
            style="max-height: calc(90vh - 70px)"
            ref="scrollContainer"
            @scroll="handleScroll"
          >
            <!-- Форма добавления отзыва -->
            <div
              v-if="canReview && currentUserId && currentUserId !== sellerId"
              class="border-b border-blue-100 p-6 bg-blue-50/30"
            >
              <h4 class="font-semibold text-slate-800 mb-3">Оставить отзыв</h4>
              <form @submit.prevent="submitReview">
                <!-- Оценка -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-slate-700 mb-2">
                    Ваша оценка
                  </label>
                  <div class="flex gap-2">
                    <button
                      v-for="star in 5"
                      :key="star"
                      type="button"
                      @click="newReview.rating = star"
                      class="focus:outline-none transition-transform hover:scale-110"
                    >
                      <i
                        :class="
                          star <= newReview.rating
                            ? 'fa-solid fa-star text-yellow-400 text-2xl'
                            : 'fa-regular fa-star text-slate-300 text-2xl'
                        "
                      ></i>
                    </button>
                  </div>
                </div>

                <!-- Комментарий -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-slate-700 mb-2">
                    Комментарий
                  </label>
                  <textarea
                    v-model="newReview.comment"
                    rows="3"
                    class="w-full px-3 py-2 border-2 border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white text-slate-800"
                    placeholder="Расскажите о своем опыте сотрудничества..."
                  ></textarea>
                </div>

                <!-- Ошибка -->
                <div v-if="error" class="mb-4 text-red-500 text-sm">
                  {{ error }}
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="!newReview.rating || submitting"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <i
                      v-if="submitting"
                      class="fa-regular fa-spinner fa-spin mr-2"
                    ></i>
                    {{ submitting ? "Отправка..." : "Отправить отзыв" }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Сообщение о невозможности оставить отзыв -->
            <div
              v-else-if="
                currentUserId &&
                currentUserId !== sellerId &&
                !canReview &&
                !loading
              "
              class="border-b border-blue-100 p-6 bg-blue-50/30 text-center"
            >
              <i class="fa-regular fa-lock text-3xl text-slate-400 mb-2"></i>
              <p class="text-slate-600">
                Вы можете оставить отзыв только после завершенной сделки с этим
                продавцом.
              </p>
            </div>

            <!-- Список отзывов -->
            <div class="p-6">
              <!-- Загрузка -->
              <div v-if="loading && !hasMore" class="text-center py-8">
                <i
                  class="fa-regular fa-spinner fa-spin text-3xl text-blue-500"
                ></i>
              </div>

              <!-- Нет отзывов -->
              <div
                v-else-if="allReviews.length === 0 && !loading"
                class="text-center py-8"
              >
                <i
                  class="fa-regular fa-comment text-5xl text-slate-400 mb-3"
                ></i>
                <p class="text-slate-500">Пока нет отзывов</p>
                <p class="text-sm text-slate-400 mt-1">
                  Будьте первым, кто оставит отзыв!
                </p>
              </div>

              <!-- Список -->
              <!-- Список -->
              <div v-else class="space-y-6">
                <div
                  v-for="review in visibleReviews"
                  :key="review.id"
                  class="border-b border-blue-100 pb-4 last:border-0"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <!-- Информация о покупателе -->
                      <div class="flex items-center gap-2 mb-2">
                        <div
                          class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center"
                        >
                          <span class="text-sm font-semibold text-blue-600">
                            {{
                              review.buyer?.name?.charAt(0).toUpperCase() || "?"
                            }}
                          </span>
                        </div>
                        <div>
                          <span class="font-semibold text-slate-800">
                            {{ review.buyer?.name || "Пользователь" }}
                          </span>
                          <RatingStars
                            :rating="review.rating"
                            :show-value="false"
                            :size="12"
                            class="mt-1"
                          />
                        </div>
                      </div>

                      <!-- Комментарий -->
                      <p class="text-slate-600 mb-2">
                        {{ review.comment || "Без комментария" }}
                      </p>

                      <!-- Дата -->
                      <div class="text-xs text-slate-400">
                        {{ formatDate(review.created_at) }}
                        <span v-if="review.is_edited" class="ml-2"
                          >(отредактировано)</span
                        >
                      </div>

                      <!-- Ответ продавца -->
                      <div
                        v-if="review.seller_response"
                        class="mt-3 pl-4 border-l-4 border-blue-400 bg-blue-50/50 p-3 rounded"
                      >
                        <div class="flex items-center gap-2 mb-1">
                          <i
                            class="fa-regular fa-reply text-blue-500 text-sm"
                          ></i>
                          <span class="text-sm font-semibold text-blue-600">
                            Ответ продавца:
                          </span>
                        </div>
                        <p class="text-sm text-slate-600">
                          {{ review.seller_response }}
                        </p>
                        <div class="text-xs text-slate-400 mt-1">
                          {{ formatDate(review.responded_at) }}
                        </div>
                      </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="flex gap-2 ml-4">
                      <!-- Редактировать отзыв -->
                      <button
                        v-if="canModify(review)"
                        @click="editReview(review)"
                        class="text-blue-500 hover:text-blue-700 transition"
                        title="Редактировать"
                      >
                        <i class="fa-regular fa-pen-to-square"></i>
                      </button>

                      <!-- Удалить отзыв -->
                      <button
                        v-if="canModify(review)"
                        @click="deleteReview(review.id)"
                        class="text-red-500 hover:text-red-700 transition"
                        title="Удалить"
                      >
                        <i class="fa-regular fa-trash-can"></i>
                      </button>

                      <!-- Ответить на отзыв -->
                      <button
                        v-if="canRespond(review)"
                        @click="openRespondForm(review)"
                        class="text-green-500 hover:text-green-700 transition"
                        title="Ответить"
                      >
                        <i class="fa-regular fa-reply"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Форма ответа -->
                  <div v-if="respondTo === review.id" class="mt-3 ml-4">
                    <textarea
                      v-model="respondText"
                      rows="2"
                      class="w-full px-3 py-2 border-2 border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white text-slate-800"
                      placeholder="Ваш ответ на отзыв..."
                    ></textarea>
                    <div class="flex gap-2 mt-2">
                      <button
                        @click="submitResponse(review.id)"
                        :disabled="!respondText.trim()"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm transition disabled:opacity-50"
                      >
                        Отправить
                      </button>
                      <button
                        @click="respondTo = null"
                        class="px-3 py-1 border-2 border-blue-200 rounded hover:bg-blue-50 text-sm transition text-slate-600"
                      >
                        Отмена
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Индикатор загрузки при подгрузке -->
                <div v-if="loading && hasMore" class="flex justify-center py-4">
                  <i
                    class="fa-regular fa-spinner fa-spin text-xl text-blue-500"
                  ></i>
                </div>

                <!-- Кнопка "Показать еще" -->
                <div
                  v-if="hasMore && !loading"
                  class="flex justify-center pt-4 pb-2"
                >
                  <button
                    @click="loadMore"
                    class="px-6 py-2.5 bg-white text-blue-500 border-2 border-blue-300 rounded-lg hover:bg-blue-50 transition-all duration-200 font-medium text-sm flex items-center gap-2 shadow-sm hover:shadow"
                  >
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                    Показать еще отзывы
                    <span class="text-blue-400"
                      >({{ allReviews.length - visibleReviews.length }})</span
                    >
                  </button>
                </div>

                <!-- Загружены все отзывы -->
                <div
                  v-if="!hasMore && allReviews.length > 0"
                  class="text-center py-4"
                >
                  <div
                    class="inline-flex items-center gap-2 text-slate-400 text-sm"
                  >
                    <i class="fa-regular fa-circle-check text-green-500"></i>
                    Все отзывы загружены
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed, nextTick } from "vue";
import axios from "axios";
import RatingStars from "./RatingStars.vue";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  sellerId: {
    type: [Number, null],
    required: false,
    default: null,
  },
  currentUserId: {
    type: Number,
    default: null,
  },
  listingType: {
    type: String,
    default: null,
  },
  listingId: {
    type: [String, Number],
    default: null,
  },
});

const emit = defineEmits(["update:show", "review-added", "review-deleted"]);

// Состояния
const loading = ref(false);
const submitting = ref(false);
const error = ref(null);
const canReview = ref(false);
const newReview = ref({ rating: 0, comment: "" });
const respondTo = ref(null);
const respondText = ref("");
const scrollContainer = ref(null);

// Для бесконечного скролла
const allReviews = ref([]);
const perPage = 2; // Изначально показываем по 2 отзыва
const visibleCount = ref(perPage);
const isLoadingMore = ref(false);

// Видимые отзывы
const visibleReviews = computed(() => {
  return allReviews.value.slice(0, visibleCount.value);
});

// Есть ли еще отзывы для загрузки
const hasMore = computed(() => {
  return visibleCount.value < allReviews.value.length;
});

// Методы
const close = () => {
  emit("update:show", false);
  respondTo.value = null;
  error.value = null;
  newReview.value = { rating: 0, comment: "" };
};

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const canModify = (review) => {
  return review.buyer_id === props.currentUserId;
};

const canRespond = (review) => {
  return review.seller_id === props.currentUserId && !review.seller_response;
};

// Загружаем больше отзывов
const loadMore = () => {
  if (isLoadingMore.value || !hasMore.value) return;

  isLoadingMore.value = true;

  setTimeout(() => {
    visibleCount.value += perPage;
    isLoadingMore.value = false;
  }, 300);
};

// Обработчик скролла
const handleScroll = () => {
  if (!scrollContainer.value) return;

  const { scrollTop, scrollHeight, clientHeight } = scrollContainer.value;

  // Загружаем еще, когда до конца контейнера остается 100px
  if (scrollHeight - scrollTop - clientHeight < 100) {
    loadMore();
  }
};

// Загружаем ВСЕ отзывы сразу (без пагинации API)
const fetchAllReviews = async () => {
  if (!props.sellerId) return;

  loading.value = true;
  try {
    const response = await axios.get(
      `/api/v1/sellers/${props.sellerId}/reviews/all`
    );
    allReviews.value = response.data.reviews || [];
    canReview.value = response.data.can_review || false;
    visibleCount.value = perPage; // Сбрасываем счетчик
  } catch (err) {
    console.error("Error fetching reviews:", err);
    // Если нет эндпоинта all, загружаем с большой пагинацией
    try {
      const response = await axios.get(
        `/api/v1/sellers/${props.sellerId}/reviews`,
        {
          params: { per_page: 1000 },
        }
      );
      allReviews.value = response.data.reviews?.data || [];
      canReview.value = response.data.can_review || false;
      visibleCount.value = perPage;
    } catch (err2) {
      console.error("Error fetching reviews (fallback):", err2);
    }
  } finally {
    loading.value = false;
  }
};

const formatModelName = (type) => {
  if (!type) return null;

  const mapping = {
    auto: "AutoListing",
    nedvizhimost: "NedvizhimostListing",
    elektronika: "ElektronikaListing",
    hobby: "HobbyListing",
    vacancy: "RabotaVacancy",
    resume: "RabotaResume",
    uslugi: "UslugiListing",
    lichnie_veschi: "LichnieVeschiListing",
    dlya_doma: "DlyaDomaListing",
  };

  return mapping[type] || type;
};

const submitReview = async () => {
  if (!props.listingType || !props.listingId) {
    error.value = "Ошибка: не указано объявление";
    return;
  }

  if (!newReview.value.rating) {
    error.value = "Пожалуйста, поставьте оценку";
    return;
  }

  submitting.value = true;
  error.value = null;

  try {
    const modelName = formatModelName(props.listingType);
    if (!modelName) {
      throw new Error(`Unknown listing type: ${props.listingType}`);
    }

    const listableType = `App\\Models\\${modelName}`;

    const { data } = await axios.post(
      `/api/v1/sellers/${props.sellerId}/reviews`,
      {
        rating: newReview.value.rating,
        comment: newReview.value.comment,
        listable_type: listableType,
        listable_id: props.listingId,
      }
    );

    newReview.value = { rating: 0, comment: "" };
    await fetchAllReviews();
    emit("review-added", data);
  } catch (err) {
    console.error("Error submitting review:", err);

    if (err.response?.data?.error) {
      error.value = err.response.data.error;
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = "Ошибка при отправке отзыва";
    }
  } finally {
    submitting.value = false;
  }
};

const editReview = async (review) => {
  const newComment = prompt("Редактировать комментарий:", review.comment);
  if (newComment !== null && newComment !== review.comment) {
    try {
      await axios.put(`/api/v1/reviews/${review.id}`, {
        rating: review.rating,
        comment: newComment,
      });
      await fetchAllReviews();
      emit("review-added");
    } catch (err) {
      console.error("Error editing review:", err);
      alert(err.response?.data?.error || "Ошибка при редактировании");
    }
  }
};

const deleteReview = async (reviewId) => {
  if (confirm("Вы уверены, что хотите удалить этот отзыв?")) {
    try {
      await axios.delete(`/api/v1/reviews/${reviewId}`);
      await fetchAllReviews();
      emit("review-deleted");
    } catch (err) {
      console.error("Error deleting review:", err);
      alert(err.response?.data?.error || "Ошибка при удалении");
    }
  }
};

const openRespondForm = (review) => {
  respondTo.value = review.id;
  respondText.value = "";
};

const submitResponse = async (reviewId) => {
  if (!respondText.value.trim()) return;

  try {
    await axios.post(`/api/v1/reviews/${reviewId}/respond`, {
      response: respondText.value,
    });
    respondTo.value = null;
    respondText.value = "";
    await fetchAllReviews();
  } catch (err) {
    console.error("Error submitting response:", err);
    alert(err.response?.data?.error || "Ошибка при отправке ответа");
  }
};

// Следим за открытием модального окна
watch(
  () => props.show,
  (newVal) => {
    if (newVal && props.sellerId) {
      fetchAllReviews();
    }
  }
);
</script>