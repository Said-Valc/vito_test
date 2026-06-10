import { ref, watch, onUnmounted, onMounted } from "vue";
import axios from "axios";

export function useChat(authUserId, recipientIdRef) {
  const messages = ref([]);
  const isTyping = ref(false);
  const isOnline = ref(false);
  let typingTimeout = null;

  const subscribe = (id) => {
    if (!window.Echo || !id) return;

    // Слушаем СВОЙ канал для получения сообщений
    window.Echo.private(`chat.${authUserId}`)
      .listen("MessageSent", (e) => {
        if (e.message.sender_id === id) {
          messages.value.push(e.message);
        }
      })
      .listenForWhisper("typing", (e) => {
        console.log("Кто-то печатает:", e); // Добавь это для теста
        if (e.userId === id) {
          isTyping.value = true;
          clearTimeout(typingTimeout);
          typingTimeout = setTimeout(() => (isTyping.value = false), 1500);
        }
      });

    // Онлайн статус
    window.Echo.join(`online`)
      .here((users) => (isOnline.value = users.some(u => u.id === id)))
      .joining((user) => { if (user.id === id) isOnline.value = true; })
      .leaving((user) => { if (user.id === id) isOnline.value = false; });
  };

  const unsubscribe = () => {
    if (window.Echo) {
      window.Echo.leave(`chat.${authUserId}`);
      window.Echo.leave(`online`);
    }
  };

  const sendMessage = async (text) => {
    const id = recipientIdRef.value;
    if (!text.trim() || !id) return;

    const tempId = Date.now();
    messages.value.push({
      id: tempId,
      text,
      sender_id: authUserId,
      created_at: new Date().toISOString(),
      pending: true,
    });

    try {
      const { data } = await axios.post(`/chat/send/${id}`, { text });
      const index = messages.value.findIndex(m => m.id === tempId);
      if (index !== -1) messages.value[index] = data;
    } catch (e) {
      const index = messages.value.findIndex(m => m.id === tempId);
      if (index !== -1) messages.value[index].error = true;
    }
  };

  const sendTyping = () => {
  const id = recipientIdRef.value;
  if (window.Echo && id) {
    // ВАЖНО: Мы шепчем в канал ПОЛУЧАТЕЛЯ
    window.Echo.private(`chat.${id}`)
      .whisper("typing", { 
        userId: authUserId // Передаем свой ID, чтобы получатель понял, кто печатает
      });
  }
};

  watch(recipientIdRef, (newId, oldId) => {
    if (oldId) unsubscribe();
    messages.value = [];
    if (newId) subscribe(newId);
  }, { immediate: true });

  onUnmounted(() => unsubscribe());

  return {
    messages,
    isTyping,
    isOnline,
    sendMessage,
    sendTyping,
  };
}