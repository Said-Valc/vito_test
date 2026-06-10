import Echo from "laravel-echo";
import Pusher from "pusher-js";
import axios from "axios";

window.Pusher = Pusher;

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Получаем CSRF токен
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    
    authEndpoint: '/broadcasting/auth',
    
    auth: {
        headers: {
            'X-CSRF-TOKEN': token?.content,
            'X-Requested-With': 'XMLHttpRequest',
        },
    },
    
    // Отключаем лишние опции Pusher
    disableStats: true,
    
    // Добавляем обработку переподключения
    reconnectionAttempts: 5,
    reconnectionDelay: 1000,
});

// Логируем подключение для отладки
if (window.Echo) {
    console.log('✅ Echo initialized');
    
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ WebSocket connected');
    });
    
    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('❌ WebSocket disconnected');
    });
    
    window.Echo.connector.pusher.connection.bind('error', (err) => {
        // Игнорируем ошибки, связанные с закрытием соединения
        if (err && err.error && err.error.data && err.error.data.code === 4201) {
            console.log('ℹ️ Connection closed, will reconnect');
            return;
        }
        console.error('❌ WebSocket error:', err);
    });
    
    window.Echo.connector.pusher.connection.bind('connecting', () => {
        console.log('🔄 WebSocket connecting...');
    });
    
    window.Echo.connector.pusher.connection.bind('unavailable', () => {
        console.log('⚠️ WebSocket unavailable');
    });
}