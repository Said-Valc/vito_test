<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  show: Boolean,
  conversations: Array,
  currentRecipient: Object,
  currentMessages: Array,
});

const emit = defineEmits(["close", "refresh"]);

const page = usePage();
const user = page.props.auth.user;

// Состояние
const activeChat = ref(null);
const messages = ref([]);
const newMessage = ref("");
const messagesContainer = ref(null);
const messagesEndRef = ref(null);
const isTyping = ref(false);
const isLoading = ref(false);
const isOnline = ref(false);
const isConnected = ref(false);

let typingTimeout = null;
let currentChannel = null;
let onlineCheckInterval = null;
let echoConnectionCheckInterval = null;

// Вычисляемые свойства
const activeRecipient = computed(
  () => activeChat.value?.user || activeChat.value
);
const activeMessages = computed(() => messages.value);

// Получить тип объявления из полиморфной связи
const getListingType = (listable) => {
  if (!listable) return null;

  const mapping = {
    "App\\Models\\AutoListing": "auto",
    "App\\Models\\NedvizhimostListing": "nedvizhimost",
    "App\\Models\\ElektronikaListing": "elektronika",
    "App\\Models\\HobbyListing": "hobby",
    "App\\Models\\RabotaVacancy": "vacancy",
    "App\\Models\\RabotaResume": "resume",
    "App\\Models\\UslugiListing": "uslugi",
    "App\\Models\\LichnieVeschiListing": "lichnie_veschi",
    "App\\Models\\DlyaDomaListing": "dlya_doma",
  };

  return mapping[listable.listable_type] || listable.listing_type || null;
};

// Форматирование времени
const formatTime = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getConversationKey = (conv) => {
  const listingId =
    conv.listable?.id || conv.listable?.listable_id || "general";

  return `${conv.user?.id}_${listingId}`;
};

// Форматирование даты для списка чатов
const formatDate = (date) => {
  if (!date) return "";
  const messageDate = new Date(date);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  if (messageDate.toDateString() === today.toDateString()) {
    return messageDate.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    });
  } else if (messageDate.toDateString() === yesterday.toDateString()) {
    return "Вчера";
  } else {
    return messageDate.toLocaleDateString();
  }
};

// Критически важная функция скролла
const forceScrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }

  if (messagesEndRef.value) {
    messagesEndRef.value.scrollIntoView({ behavior: "auto", block: "end" });
  }

  setTimeout(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
    if (messagesEndRef.value) {
      messagesEndRef.value.scrollIntoView({ behavior: "auto", block: "end" });
    }
  }, 50);

  setTimeout(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  }, 100);
};

// Загрузка сообщений для выбранного чата
const loadMessages = async (conversation) => {
  isLoading.value = true;

  try {
    const params = {};

    if (conversation.listable) {
      const listable = conversation.listable;
      params.listing_type = listable.listing_type || getListingType(listable);
      params.listing_id = listable.id || listable.listable_id;
    }

    console.log("📥 Загружаем сообщения с параметрами:", {
      recipient: conversation.user.id,
      params,
    });

    const response = await axios.get(`/chat/messages/${conversation.user.id}`, {
      params,
    });

    messages.value = response.data;

    await nextTick();
    forceScrollToBottom();

    await markMessagesAsRead(conversation);
  } catch (error) {
    console.error("Error loading messages:", error);
  } finally {
    isLoading.value = false;
  }
};

// Отправка сообщения
const sendMessage = async () => {
  if (!newMessage.value.trim() || !activeRecipient.value) return;

  const text = newMessage.value;
  const tempId = Date.now();

  const tempMessage = {
    id: tempId,
    text: text,
    sender_id: user.id,
    receiver_id: activeRecipient.value.id,
    created_at: new Date().toISOString(),
    pending: true,
  };

  messages.value.push(tempMessage);
  newMessage.value = "";

  await nextTick();
  forceScrollToBottom();

  try {
    const payload = {
      text: text,
    };

    if (activeChat.value?.listable) {
      const listable = activeChat.value.listable;
      payload.listing_type = listable.listing_type || getListingType(listable);
      payload.listing_id = listable.id || listable.listable_id;
      payload.subject = activeChat.value.subject || listable.title;
    }

    console.log("📤 Отправляем сообщение с параметрами:", payload);

    const { data } = await axios.post(
      `/chat/send/${activeRecipient.value.id}`,
      payload
    );

    const index = messages.value.findIndex((m) => m.id === tempId);
    if (index !== -1) {
      messages.value[index] = data;
    }

    updateConversationList(activeRecipient.value, data);

    await nextTick();
    forceScrollToBottom();

    emit("refresh");
  } catch (error) {
    console.error("Error sending message:", error);
    const index = messages.value.findIndex((m) => m.id === tempId);
    if (index !== -1) {
      messages.value[index].error = true;
    }
  }
};

// Обновление списка чатов после отправки
const updateConversationList = (recipient, message) => {
  if (!props.conversations) return;

  const convIndex = props.conversations.findIndex(
    (c) => c.user?.id === recipient.id
  );

  if (convIndex !== -1 && props.conversations[convIndex]) {
    props.conversations[convIndex].last_message = message;
    props.conversations[convIndex].updated_at = message.created_at;
  }
};

// Отправка статуса печатания
const sendTyping = () => {
  if (!activeRecipient.value || !window.Echo) return;

  window.Echo.private(`chat.${activeRecipient.value.id}`).whisper("typing", {
    userId: user.id,
  });
};

const checkEchoConnection = () => {
  if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
    const connection = window.Echo.connector.pusher.connection;
    isConnected.value = connection.state === "connected";
    console.log("🔌 WebSocket status:", connection.state);
  }
};

// Подписка на канал для получения сообщений и статуса печатания
const subscribeToMessagesChannel = (recipientId) => {
  if (!window.Echo || !recipientId || !user) {
    console.error("❌ Cannot subscribe: Echo not initialized");
    return;
  }

  console.log("📡 Subscribing to channel for user:", user.id);

  if (currentChannel) {
    console.log("📡 Leaving previous channel");
    window.Echo.leave(`chat.${user.id}`);
  }

  currentChannel = window.Echo.private(`chat.${user.id}`)
    .listen(".MessageSent", (e) => {
      console.log("📨 Received message via WebSocket:", e);

      const message = e.message;

      if (message.sender_id === recipientId) {
        const currentListingType =
          activeChat.value?.listable?.listing_type ||
          getListingType(activeChat.value?.listable);
        const currentListingId =
          activeChat.value?.listable?.id ||
          activeChat.value?.listable?.listable_id;

        const messageListingType = message.listable?.listing_type;
        const messageListingId = message.listable?.id;

        console.log("📨 Checking message match:", {
          currentListingType,
          currentListingId,
          messageListingType,
          messageListingId,
        });

        if (
          (!currentListingId && !messageListingId) ||
          (currentListingType === messageListingType &&
            currentListingId == messageListingId)
        ) {
          const exists = messages.value.some((m) => m.id === message.id);
          if (!exists) {
            console.log("✅ Adding new message to chat");
            messages.value.push(message);
            nextTick(() => {
              forceScrollToBottom();
            });
            markMessagesAsRead(activeChat.value);
            emit("refresh");
          } else {
            console.log("⚠️ Message already exists");
          }
        }
      } else {
        console.log("📨 Message from other user, refreshing conversations");
        emit("refresh");
      }
    })
    .listenForWhisper("typing", (e) => {
      console.log("⌨️ Typing event received:", e);
      if (e.userId === recipientId) {
        isTyping.value = true;

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
          isTyping.value = false;
        }, 800);
      }
    })
    .subscribed(() => {
      console.log("✅ Successfully subscribed to channel");
      isConnected.value = true;
    })
    .error((error) => {
      console.error("❌ Channel subscription error:", error);
      isConnected.value = false;
    });
};

// Проверка онлайн статуса через API
const checkOnlineStatus = async () => {
  if (!activeRecipient.value) return;

  try {
    const response = await axios.get(
      `/chat/online-status/${activeRecipient.value.id}`
    );
    isOnline.value = response.data.online;
  } catch (error) {
    console.error("Error checking online status:", error);
    isOnline.value = false;
  }
};

const startOnlineCheck = () => {
  if (onlineCheckInterval) {
    clearInterval(onlineCheckInterval);
  }

  onlineCheckInterval = setInterval(() => {
    if (activeRecipient.value && props.show) {
      checkOnlineStatus();
      checkEchoConnection();
    }
  }, 30000);
};

const stopOnlineCheck = () => {
  if (onlineCheckInterval) {
    clearInterval(onlineCheckInterval);
    onlineCheckInterval = null;
  }

  if (echoConnectionCheckInterval) {
    clearInterval(echoConnectionCheckInterval);
    echoConnectionCheckInterval = null;
  }
};

// Помечаем сообщения как прочитанные
const markMessagesAsRead = async (conversation) => {
  try {
    const payload = {};

    if (conversation.listable) {
      const listable = conversation.listable;
      payload.listing_type = listable.listing_type || getListingType(listable);
      payload.listing_id = listable.id || listable.listable_id;
    }

    console.log("✓ Отмечаем как прочитанные с параметрами:", payload);

    await axios.post(`/chat/mark-as-read/${conversation.user.id}`, payload);

    emit("refresh");
  } catch (error) {
    console.error("Error marking messages as read:", error);
  }
};

// Выбор чата
const selectChat = async (conversation) => {
  if (!conversation?.user) return;

  console.log("💬 Selecting chat:", conversation);

  activeChat.value = conversation;
  isTyping.value = false;

  subscribeToMessagesChannel(conversation.user.id);
  await checkOnlineStatus();

  await loadMessages(conversation);
};

// Закрытие модального окна
const closeModal = () => {
  stopOnlineCheck();

  if (currentChannel) {
    try {
      window.Echo.leave(`chat.${user.id}`);
      console.log("📡 Left channel");
    } catch (error) {
      console.error("Error leaving channel:", error);
    }
    currentChannel = null;
  }
  emit("close");
};

// Закрытие по Escape
const handleEscape = (e) => {
  if (e.key === "Escape" && props.show) {
    closeModal();
  }
};

// Следим за открытием модального окна
watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      setTimeout(() => {
        forceScrollToBottom();
      }, 100);

      setTimeout(() => {
        forceScrollToBottom();
      }, 300);

      if (activeRecipient.value) {
        checkOnlineStatus();
        subscribeToMessagesChannel(activeRecipient.value.id);
        startOnlineCheck();
      }
      emit("refresh");

      checkEchoConnection();
    } else {
      stopOnlineCheck();
    }
  }
);

// Следим за сообщениями
watch(
  messages,
  () => {
    nextTick(() => {
      forceScrollToBottom();
    });
  },
  { deep: true }
);

// Следим за сменой активного получателя
watch(activeRecipient, (newRecipient, oldRecipient) => {
  if (newRecipient && newRecipient.id !== oldRecipient?.id) {
    checkOnlineStatus();
    subscribeToMessagesChannel(newRecipient.id);
  }
});

onMounted(() => {
  document.addEventListener("keydown", handleEscape);

  checkEchoConnection();

  echoConnectionCheckInterval = setInterval(checkEchoConnection, 5000);

  if (props.currentRecipient && props.currentMessages) {
    activeChat.value = {
      user: props.currentRecipient,
      messages: props.currentMessages,
      last_message: props.currentMessages[props.currentMessages.length - 1],
    };
    messages.value = props.currentMessages;
    subscribeToMessagesChannel(props.currentRecipient.id);
    checkOnlineStatus();
    startOnlineCheck();

    setTimeout(() => {
      forceScrollToBottom();
    }, 200);
  } else if (props.conversations && props.conversations.length > 0) {
    selectChat(props.conversations[0]);
    startOnlineCheck();
  }
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleEscape);
  stopOnlineCheck();

  if (currentChannel) {
    try {
      window.Echo.leave(`chat.${user.id}`);
    } catch (error) {
      console.error("Error leaving channel:", error);
    }
  }
});
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
        @click.self="closeModal"
      >
        <div
          class="bg-white rounded-lg shadow-xl w-full max-w-5xl h-[85vh] flex overflow-hidden border-2 border-blue-200"
        >
          <!-- Список чатов (сайдбар) -->
          <div class="w-80 border-r border-blue-100 bg-white flex flex-col">
            <div class="p-4 border-b border-blue-100 bg-white sticky top-0">
              <h3
                class="text-lg font-semibold flex items-center justify-between text-slate-800"
              >
                Сообщения
                <span
                  v-if="!isConnected"
                  class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded"
                  title="Проблемы с подключением WebSocket"
                >
                  ⚠️ Офлайн
                </span>
              </h3>
            </div>

            <div class="flex-1 overflow-y-auto">
              <div
                v-for="conv in conversations"
                :key="getConversationKey(conv)"
                @click="selectChat(conv)"
                :class="[
                  'p-4 border-b border-blue-50 cursor-pointer transition hover:bg-blue-50',
                  activeChat?.user?.id === conv.user?.id &&
                  (!activeChat?.listable?.id ||
                    activeChat?.listable?.id ===
                      (conv.listable?.id || conv.listable?.listable_id))
                    ? 'bg-blue-50 border-blue-200'
                    : 'bg-white',
                ]"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <p class="font-semibold truncate text-slate-800">
                        {{ conv.user?.name || "Пользователь" }}
                      </p>
                      <span
                        v-if="
                          activeChat?.user?.id === conv.user?.id && isOnline
                        "
                        class="w-2 h-2 bg-green-500 rounded-full"
                      ></span>
                    </div>
                    <p
                      v-if="conv.subject || conv.listable?.title"
                      class="text-xs text-blue-600 truncate mb-1"
                    >
                      {{ conv.subject || conv.listable?.title }}
                    </p>
                    <p class="text-sm text-slate-600 truncate">
                      {{ conv.last_message?.text || "Нет сообщений" }}
                    </p>
                    <p
                      v-if="conv.last_message?.created_at"
                      class="text-xs text-slate-400 mt-1"
                    >
                      {{ formatDate(conv.last_message.created_at) }}
                    </p>
                  </div>
                  <span
                    v-if="conv.unread_count > 0"
                    class="bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-2 flex-shrink-0"
                  >
                    {{ conv.unread_count > 9 ? "9+" : conv.unread_count }}
                  </span>
                </div>
              </div>

              <div
                v-if="!conversations?.length"
                class="p-8 text-center text-slate-500"
              >
                У вас пока нет диалогов
              </div>
            </div>
          </div>

          <!-- Область чата -->
          <div class="flex-1 flex flex-col min-w-0">
            <div v-if="activeChat" class="flex-1 flex flex-col h-full">
              <!-- Заголовок чата -->
              <div
                class="p-4 border-b border-blue-100 bg-white flex justify-between items-center flex-shrink-0"
              >
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <h4 class="font-semibold truncate text-slate-800">
                      {{ activeRecipient?.name }}
                    </h4>
                    <span
                      v-if="isOnline"
                      class="w-2 h-2 bg-green-500 rounded-full"
                    ></span>
                    <span
                      v-if="!isOnline"
                      class="w-2 h-2 bg-gray-400 rounded-full"
                    ></span>
                    <p
                      class="text-sm"
                      :class="isOnline ? 'text-green-500' : 'text-gray-400'"
                    >
                      {{ isOnline ? "в сети" : "не в сети" }}
                    </p>
                  </div>
                  <p
                    v-if="activeChat.subject || activeChat.listable?.title"
                    class="text-sm text-blue-600 truncate"
                  >
                    {{ activeChat.subject || activeChat.listable?.title }}
                  </p>
                </div>
                <button
                  @click="closeModal"
                  class="text-slate-400 hover:text-slate-600 text-2xl leading-none ml-4 flex-shrink-0"
                >
                  ×
                </button>
              </div>

              <!-- Сообщения -->
              <div
                ref="messagesContainer"
                class="flex-1 overflow-y-auto bg-white"
                style="min-height: 0; height: 100%"
              >
                <div class="p-4 space-y-3">
                  <div
                    v-for="msg in activeMessages"
                    :key="msg.id"
                    class="flex"
                    :class="
                      msg.sender_id === user.id
                        ? 'justify-end'
                        : 'justify-start'
                    "
                  >
                    <div
                      :class="[
                        'max-w-[70%] px-3 py-2 rounded-lg',
                        msg.sender_id === user.id
                          ? 'bg-blue-500 text-white'
                          : 'bg-gray-100 text-gray-900 border border-gray-200',
                      ]"
                    >
                      <div class="break-words">
                        {{ msg.text }}
                        <span v-if="msg.pending" class="ml-1 text-xs">⏳</span>
                        <span v-if="msg.error" class="ml-1 text-xs text-red-500"
                          >❌</span
                        >
                      </div>
                      <div class="text-xs opacity-70 text-right mt-1">
                        {{ formatTime(msg.created_at) }}
                      </div>
                    </div>
                  </div>

                  <!-- Индикатор печатания -->
                  <div v-if="isTyping" class="flex justify-start">
                    <div
                      class="bg-gray-100 rounded-lg px-3 py-2 border border-gray-200"
                    >
                      <div class="flex items-center gap-2">
                        <div class="flex space-x-1">
                          <div
                            class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"
                          ></div>
                          <div
                            class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"
                            style="animation-delay: 0.1s"
                          ></div>
                          <div
                            class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"
                            style="animation-delay: 0.2s"
                          ></div>
                        </div>
                        <p class="text-sm text-slate-500 italic">
                          {{ activeRecipient?.name }} печатает...
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Загрузка -->
                  <div v-if="isLoading" class="flex justify-center">
                    <div class="text-slate-500">Загрузка сообщений...</div>
                  </div>

                  <!-- Якорь для скролла -->
                  <div ref="messagesEndRef"></div>
                </div>
              </div>

              <!-- Поле ввода -->
              <form
                @submit.prevent="sendMessage"
                class="p-4 border-t border-blue-100 bg-white flex-shrink-0"
              >
                <div class="flex gap-2">
                  <input
                    v-model="newMessage"
                    @input="sendTyping"
                    type="text"
                    class="flex-1 border-2 border-blue-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white"
                    placeholder="Написать сообщение..."
                    autofocus
                  />
                  <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition disabled:opacity-50"
                    :disabled="!newMessage.trim()"
                  >
                    Отправить
                  </button>
                </div>
              </form>
            </div>

            <div
              v-else
              class="flex-1 flex items-center justify-center text-slate-500"
            >
              Выберите чат для начала общения
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .bg-white,
.modal-leave-active .bg-white {
  transition: transform 0.3s ease;
}

.modal-enter-from .bg-white,
.modal-leave-to .bg-white {
  transform: scale(0.95);
}

.overflow-y-auto {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.break-words {
  word-break: break-word;
  overflow-wrap: break-word;
}

@keyframes bounce {
  0%,
  80%,
  100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

.animate-bounce {
  animation: bounce 1.4s infinite ease-in-out both;
}
</style>