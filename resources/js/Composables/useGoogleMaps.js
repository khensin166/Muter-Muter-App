/**
 * Lazy Google Maps JS API loader (loaded once, only on pages that need a map).
 * Key: VITE_GOOGLE_MAPS_API_KEY in .env
 */
let loaderPromise = null;

export const MAP_STYLE_NATURE = [
  { featureType: 'poi.business', stylers: [{ visibility: 'off' }] },
  { featureType: 'transit', stylers: [{ visibility: 'simplified' }] },
  { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#a7d3d6' }] },
  { featureType: 'landscape.natural', elementType: 'geometry', stylers: [{ color: '#e8f0e3' }] },
  { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
  { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#f3e3c3' }] },
];

export function loadGoogleMaps() {
  if (typeof window === 'undefined') return Promise.reject(new Error('No window'));
  if (window.google?.maps) return Promise.resolve(window.google.maps);
  if (loaderPromise) return loaderPromise;

  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
  if (!apiKey) return Promise.reject(new Error('VITE_GOOGLE_MAPS_API_KEY belum diatur'));

  loaderPromise = new Promise((resolve, reject) => {
    const callbackName = '__betaTudiaMapsReady';
    window[callbackName] = () => {
      resolve(window.google.maps);
      delete window[callbackName];
    };
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=${callbackName}&loading=async&language=id&region=ID`;
    script.async = true;
    script.defer = true;
    script.onerror = () => {
      loaderPromise = null;
      reject(new Error('Gagal memuat Google Maps'));
    };
    document.head.appendChild(script);
  });

  return loaderPromise;
}

export function useGoogleMaps() {
  return { loadGoogleMaps, MAP_STYLE_NATURE };
}

