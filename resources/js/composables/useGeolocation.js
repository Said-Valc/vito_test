import { ref } from 'vue';

export function useGeolocation() {
  const userLocation = ref(null);
  const locationError = ref(null);
  const isLoading = ref(false);

  // Получаем геолокацию пользователя
  const getUserLocation = () => {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error('Геолокация не поддерживается'));
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          });
        },
        (error) => {
          reject(error);
        },
        {
          enableHighAccuracy: false,
          timeout: 5000,
          maximumAge: 300000, // 5 минут кэш
        }
      );
    });
  };

  // Сохраняем локацию в localStorage
  const saveLocation = (location) => {
    if (location) {
      localStorage.setItem('user_location', JSON.stringify(location));
    }
  };

  // Загружаем локацию из localStorage
  const loadLocation = () => {
    const saved = localStorage.getItem('user_location');
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch (e) {
        return null;
      }
    }
    return null;
  };

  // Вычисляем расстояние между двумя точками в км
  const calculateDistance = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return null;

    const R = 6371; // Радиус Земли в км
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(toRad(lat1)) *
        Math.cos(toRad(lat2)) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;

    return Math.round(distance * 10) / 10; // Округляем до 1 знака
  };

  const toRad = (deg) => {
    return deg * (Math.PI / 180);
  };

  // Форматируем расстояние для отображения
  const formatDistance = (distance) => {
    if (distance === null || distance === undefined) return '';

    if (distance < 1) {
      return `${Math.round(distance * 1000)} м`;
    } else if (distance < 10) {
      return `${distance.toFixed(1)} км`;
    } else {
      return `${Math.round(distance)} км`;
    }
  };

  // Инициализация
  const init = async () => {
    isLoading.value = true;

    try {
      // Пробуем загрузить сохраненную локацию
      let location = loadLocation();
      
      if (!location) {
        // Если нет сохраненной, запрашиваем новую
        location = await getUserLocation();
        saveLocation(location);
      }

      userLocation.value = location;
    } catch (error) {
      locationError.value = error;
      console.warn('Не удалось получить геолокацию:', error.message);
    } finally {
      isLoading.value = false;
    }
  };

  // Обновить локацию
  const refreshLocation = async () => {
    try {
      const location = await getUserLocation();
      userLocation.value = location;
      saveLocation(location);
      locationError.value = null;
    } catch (error) {
      locationError.value = error;
    }
  };

  return {
    userLocation,
    locationError,
    isLoading,
    init,
    refreshLocation,
    calculateDistance,
    formatDistance,
  };
}