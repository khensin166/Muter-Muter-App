<script setup>
/**
 * ItineraryMap — interactive Google Map for a tour itinerary.
 * Props:
 *  - itinerary: [{ id, day_number, title, stops: [{ id, title, latitude, longitude, destination_name, destination_image_url, destination_slug }] }]
 *  - activeDay: number | null (null = all days)
 *  - activeStopId: string | null
 * Emits:
 *  - select-stop(stopId)
 *  - update:activeDay(dayNumber | null)
 */
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { loadGoogleMaps, MAP_STYLE_NATURE } from '@/Composables/useGoogleMaps';

const props = defineProps({
  itinerary: { type: Array, default: () => [] },
  activeDay: { type: Number, default: null },
  activeStopId: { type: String, default: null },
  isSingleDestination: { type: Boolean, default: false },
});

const emit = defineEmits(['select-stop', 'update:activeDay']);

const DAY_COLORS = ['#0f766e', '#c2410c', '#7c3aed', '#be123c', '#0369a1', '#a16207', '#15803d'];
const dayColor = (dayNumber) => DAY_COLORS[(dayNumber - 1) % DAY_COLORS.length];

const mapEl = ref(null);
const status = ref('loading'); // loading | ready | error | empty
const errorMessage = ref('');

let gmaps = null;
let map = null;
let infoWindow = null;
let markers = new Map(); // stopId -> { marker, stop, day }
let polylines = [];

const geoStops = computed(() =>
  props.itinerary.flatMap((day) =>
    (day.stops || [])
      .filter((s) => Number.isFinite(s.latitude) && Number.isFinite(s.longitude))
      .map((s, idx) => ({ ...s, day_number: day.day_number, order: idx + 1 })),
  ),
);

const visibleStops = computed(() =>
  props.activeDay ? geoStops.value.filter((s) => s.day_number === props.activeDay) : geoStops.value,
);

const days = computed(() => props.itinerary.map((d) => d.day_number));

const singleMode = computed(
  () => props.isSingleDestination || (days.value.length <= 1 && geoStops.value.length <= 1),
);

const escapeHtml = (str) =>
  String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

const infoContent = (stop) => `
  <div style="max-width:220px;font-family:inherit">
    ${stop.destination_image_url ? `<img src="${escapeHtml(stop.destination_image_url)}" style="width:100%;height:104px;object-fit:cover;border-radius:8px;margin-bottom:6px" />` : ''}
    ${!singleMode.value ? `<div style="font-size:10px;font-weight:700;color:${dayColor(stop.day_number)};text-transform:uppercase">Hari ${stop.day_number} • Stop ${stop.order}</div>` : ''}
    <div style="font-size:13px;font-weight:800;color:#0f172a;margin-top:2px">${escapeHtml(stop.title)}</div>
    ${stop.destination_name ? `<div style="font-size:11px;color:#64748b">📍 ${escapeHtml(stop.destination_name)}</div>` : ''}
    <div style="margin-top:6px;display:flex;gap:8px">
      ${stop.destination_slug ? `<a href="/destinations/${escapeHtml(stop.destination_slug)}" style="font-size:11px;font-weight:700;color:#0f766e">Detail →</a>` : ''}
      <a href="https://www.google.com/maps/search/?api=1&query=${stop.latitude},${stop.longitude}" target="_blank" rel="noopener" style="font-size:11px;font-weight:700;color:#0f766e">Buka di Google Maps →</a>
    </div>
  </div>`;

const clearOverlays = () => {
  markers.forEach(({ marker }) => marker.setMap(null));
  markers = new Map();
  polylines.forEach((p) => p.setMap(null));
  polylines = [];
};

const render = () => {
  if (!map || !gmaps) return;
  clearOverlays();

  if (visibleStops.value.length === 0) {
    status.value = geoStops.value.length === 0 ? 'empty' : 'ready';
    return;
  }
  status.value = 'ready';

  const bounds = new gmaps.LatLngBounds();
  const byDay = new Map();

  visibleStops.value.forEach((stop) => {
    const position = { lat: stop.latitude, lng: stop.longitude };
    const marker = new gmaps.Marker({
      position,
      map,
      title: stop.title,
      label: singleMode.value
        ? undefined
        : { text: String(stop.order), color: '#fff', fontSize: '11px', fontWeight: '700' },
      icon: {
        path: gmaps.SymbolPath.CIRCLE,
        scale: singleMode.value ? 10 : 12,
        fillColor: dayColor(stop.day_number),
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 2.5,
      },
    });
    marker.addListener('click', () => {
      openInfo(stop.id);
      emit('select-stop', stop.id);
    });
    markers.set(stop.id, { marker, stop });
    bounds.extend(position);

    if (!byDay.has(stop.day_number)) byDay.set(stop.day_number, []);
    byDay.get(stop.day_number).push(position);
  });

  byDay.forEach((path, dayNumber) => {
    if (path.length < 2) return;
    polylines.push(
      new gmaps.Polyline({
        path,
        map,
        strokeColor: dayColor(dayNumber),
        strokeOpacity: 0,
        icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 0.9, strokeWeight: 3, scale: 3 }, offset: '0', repeat: '12px' }],
      }),
    );
  });

  if (visibleStops.value.length === 1) {
    map.panTo(bounds.getCenter());
    map.setZoom(13);
  } else {
    map.fitBounds(bounds, 48);
  }
};

const openInfo = (stopId) => {
  const entry = markers.get(stopId);
  if (!entry || !infoWindow) return;
  infoWindow.setContent(infoContent(entry.stop));
  infoWindow.open({ map, anchor: entry.marker });
  map.panTo(entry.marker.getPosition());
};

onMounted(async () => {
  if (geoStops.value.length === 0) {
    status.value = 'empty';
    return;
  }
  try {
    gmaps = await loadGoogleMaps();
    map = new gmaps.Map(mapEl.value, {
      center: { lat: 2.66, lng: 98.85 }, // Danau Toba
      zoom: 9,
      styles: MAP_STYLE_NATURE,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true,
      gestureHandling: 'cooperative',
    });
    infoWindow = new gmaps.InfoWindow();
    render();
  } catch (e) {
    status.value = 'error';
    errorMessage.value = e.message;
  }
});

onBeforeUnmount(clearOverlays);

watch(() => props.activeDay, () => {
  infoWindow?.close();
  render();
});

watch(() => props.activeStopId, (id) => {
  if (!id) return;
  const stop = geoStops.value.find((s) => s.id === id);
  if (stop && props.activeDay && stop.day_number !== props.activeDay) {
    emit('update:activeDay', stop.day_number);
    setTimeout(() => openInfo(id), 50);
  } else {
    openInfo(id);
  }
});
</script>

<template>
  <div class="flex flex-col h-full rounded-2xl border border-border overflow-hidden bg-surface">
    <!-- Day filter tabs (only rendered for multi-day or multi-stop itineraries) -->
    <div
      v-if="!singleMode"
      class="flex items-center gap-1.5 p-2 border-b border-border overflow-x-auto bg-surface-muted/60"
    >
      <button
        type="button"
        @click="emit('update:activeDay', null)"
        class="px-3 py-1.5 rounded-lg text-[11px] font-bold whitespace-nowrap transition cursor-pointer"
        :class="activeDay === null ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
      >
        Semua Hari
      </button>
      <button
        v-for="d in days"
        :key="d"
        type="button"
        @click="emit('update:activeDay', d)"
        class="px-3 py-1.5 rounded-lg text-[11px] font-bold whitespace-nowrap transition cursor-pointer inline-flex items-center gap-1.5"
        :class="activeDay === d ? 'text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
        :style="activeDay === d ? { backgroundColor: dayColor(d) } : {}"
      >
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: activeDay === d ? '#fff' : dayColor(d) }"></span>
        Hari {{ d }}
      </button>
    </div>

    <!-- Map canvas -->
    <div class="relative flex-1 min-h-[320px]">
      <div ref="mapEl" class="absolute inset-0"></div>

      <div v-if="status === 'loading'" class="absolute inset-0 flex items-center justify-center bg-slate-50 text-xs text-slate-500">
        <span class="animate-pulse">Memuat peta rute…</span>
      </div>
      <div v-else-if="status === 'empty'" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50 text-center p-6">
        <span class="text-2xl">🗺️</span>
        <p class="text-xs font-semibold text-slate-600 mt-2">Koordinat lokasi rute belum tersedia</p>
      </div>
      <div v-else-if="status === 'error'" class="absolute inset-0 flex flex-col items-center justify-center bg-rose-50 text-center p-6">
        <span class="text-2xl">⚠️</span>
        <p class="text-xs font-semibold text-rose-700 mt-2">Peta gagal dimuat</p>
        <p class="text-[11px] text-rose-500 mt-0.5">{{ errorMessage }}</p>
      </div>
    </div>

    <div class="px-3 py-2 border-t border-border text-[10px] text-text-muted flex items-center gap-3">
      <span>● Nomor = urutan stop</span>
      <span>┄ Garis = rute per hari</span>
    </div>
  </div>
</template>

