<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ItineraryMap from '@/Components/ItineraryMap.vue';
import { useTrans } from '@/Composables/useTrans';
import { ref, computed } from 'vue';

const { t } = useTrans();

const props = defineProps({
  destination: Object,
  nearbyDestinations: Array,
  relatedTours: Array,
});

const activeMediaIndex = ref(0);

const mediaList = computed(() => {
  return props.destination.media && props.destination.media.length > 0
    ? props.destination.media
    : [{ id: 'fallback', url: 'https://images.unsplash.com/photo-1578469550956-0e16b69c6a3d?auto=format&fit=crop&w=1200&q=80' }];
});

const activeImageUrl = computed(() => {
  return mediaList.value[activeMediaIndex.value]?.url || mediaList.value[0]?.url;
});

const singleStopItinerary = computed(() => {
  if (!props.destination?.latitude || !props.destination?.longitude) return [];
  return [
    {
      id: 'dest-day-1',
      day_number: 1,
      title: props.destination.name,
      stops: [
        {
          id: props.destination.id,
          title: props.destination.name,
          latitude: Number(props.destination.latitude),
          longitude: Number(props.destination.longitude),
          destination_name: props.destination.cluster_label,
          destination_image_url: activeImageUrl.value,
          destination_slug: null,
        },
      ],
    },
  ];
});
</script>

<template>
  <AppLayout>
    <Head :title="`${destination.name} — Wisata Danau Toba`" />

    <!-- Breadcrumb & Hero -->
    <div class="bg-surface-muted border-b border-border py-4 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center gap-2 text-xs text-text-muted">
        <Link href="/" class="hover:text-primary">{{ t('nav.home') }}</Link>
        <span>/</span>
        <Link href="/destinations" class="hover:text-primary">{{ t('nav.explore_destinations') }}</Link>
        <span>/</span>
        <span class="text-text-main font-semibold truncate">{{ destination.name }}</span>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Interactive Dynamic Gallery -->
          <div class="space-y-3">
            <!-- Main Featured Image -->
            <div class="relative rounded-3xl overflow-hidden shadow-card border border-border h-80 sm:h-[420px] bg-slate-900 group">
              <img
                :src="activeImageUrl"
                :alt="destination.name"
                class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20 pointer-events-none"></div>

              <!-- Top Left Badges -->
              <div class="absolute top-4 left-4 flex flex-wrap items-center gap-2">
                <span class="bg-primary/90 text-white backdrop-blur-md text-xs font-bold px-3 py-1 rounded-full shadow-md">
                  {{ destination.cluster_label }}
                </span>
                <span class="bg-white/90 text-slate-800 backdrop-blur-md text-xs font-bold px-3 py-1 rounded-full shadow-md">
                  {{ destination.category || t('home.default_category') }}
                </span>
              </div>

              <!-- Bottom Right Photo Counter -->
              <div class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-md text-white text-xs font-medium px-3 py-1.5 rounded-xl border border-white/20 flex items-center gap-1.5 shadow-sm">
                <span>📷</span>
                <span>{{ activeMediaIndex + 1 }} / {{ mediaList.length }} {{ t('destinations.photos_badge') || 'Foto' }}</span>
              </div>
            </div>

            <!-- Thumbnail Strip -->
            <div v-if="mediaList.length > 1" class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin">
              <button
                v-for="(media, idx) in mediaList"
                :key="media.id"
                type="button"
                @click="activeMediaIndex = idx"
                :class="[
                  'relative shrink-0 w-24 h-16 sm:w-28 sm:h-20 rounded-xl overflow-hidden border-2 transition-all cursor-pointer',
                  activeMediaIndex === idx
                    ? 'border-primary ring-2 ring-primary/40 shadow-md scale-105'
                    : 'border-border opacity-70 hover:opacity-100 hover:border-primary/50'
                ]"
              >
                <img :src="media.url" :alt="destination.name" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Header Info -->
          <div>
            <h1 class="text-2xl sm:text-3xl font-black text-text-main">
              {{ destination.name }}
            </h1>
          </div>

          <!-- Description -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h2 class="text-base font-bold text-text-main mb-3">{{ t('destinations.about_title') }}</h2>
            <p class="text-sm text-text-muted leading-relaxed whitespace-pre-line">
              {{ destination.description }}
            </p>
          </div>

          <!-- Interactive Google Map Location -->
          <div v-if="singleStopItinerary.length > 0" class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-3">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-bold text-text-main">Peta Lokasi Destinasi</h2>
              <a
                :href="`https://www.google.com/maps/search/?api=1&query=${destination.latitude},${destination.longitude}`"
                target="_blank"
                rel="noopener noreferrer"
                class="text-xs font-bold text-primary hover:underline"
              >
                Buka di Google Maps →
              </a>
            </div>
            <div class="h-72">
              <ItineraryMap :itinerary="singleStopItinerary" :is-single-destination="true" />
            </div>
          </div>

          <!-- Related Tours Visiting This Destination -->
          <div v-if="relatedTours.length > 0" class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h2 class="text-base font-bold text-text-main mb-3">{{ t('destinations.related_tours_title') }}</h2>
            <div class="space-y-3">
              <Link
                v-for="tour in relatedTours"
                :key="tour.id"
                :href="`/tours/${tour.slug}`"
                class="flex items-center justify-between p-3.5 rounded-xl border border-border hover:border-primary bg-surface-muted/50 transition group"
              >
                <div>
                  <h4 class="text-sm font-bold text-text-main group-hover:text-primary transition-colors">
                    {{ tour.name }}
                  </h4>
                  <span class="text-xs text-text-muted">{{ t('vehicles.days_unit', { days: tour.duration_days }) }}</span>
                </div>
                <div class="text-right">
                  <span class="text-xs text-text-muted block">{{ t('home.starting_from') }}</span>
                  <span class="text-sm font-black text-primary">{{ formatRupiah(tour.base_price) }}</span>
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- Sidebar Info (1 Col) -->
        <div class="space-y-6">
          <!-- Quick Facts Card -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h3 class="text-base font-bold text-text-main mb-4 pb-3 border-b border-border">
              {{ t('destinations.visit_info_title') }}
            </h3>

            <div class="space-y-4 text-xs">
              <div>
                <span class="text-text-muted block font-semibold mb-1">{{ t('destinations.domestic_ticket') }}</span>
                <span class="text-base font-black text-primary">
                  {{ destination.ticket_price_domestic > 0 ? formatRupiah(destination.ticket_price_domestic) : t('home.free') }}
                </span>
              </div>

              <div v-if="destination.ticket_price_foreign > 0">
                <span class="text-text-muted block font-semibold mb-1">{{ t('destinations.foreign_ticket') }}</span>
                <span class="text-sm font-bold text-slate-700">
                  {{ formatRupiah(destination.ticket_price_foreign) }}
                </span>
              </div>

              <div class="pt-3 border-t border-border">
                <span class="text-text-muted block font-semibold mb-1">{{ t('destinations.operating_hours') }}</span>
                <span class="text-sm font-medium text-text-main">
                  🕒 {{ destination.operating_hours || '24 Jam' }}
                </span>
              </div>

              <div class="pt-3 border-t border-border">
                <span class="text-text-muted block font-semibold mb-1">GPS</span>
                <span class="text-xs font-mono text-slate-600 block">
                  {{ destination.latitude }}, {{ destination.longitude }}
                </span>
                <a
                  :href="`https://maps.google.com/?q=${destination.latitude},${destination.longitude}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-accent hover:underline"
                >
                  <span>📍</span>
                  <span>{{ t('destinations.open_google_maps') }}</span>
                </a>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-border">
              <Link
                href="/tours"
                class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-2.5 px-4 rounded-xl text-center text-xs block transition"
              >
                {{ t('destinations.find_related_tours') }}
              </Link>
            </div>
          </div>

          <!-- Nearby in the same cluster -->
          <div v-if="nearbyDestinations.length > 0" class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h3 class="text-sm font-bold text-text-main mb-3 pb-2 border-b border-border">
              {{ t('destinations.nearby_title') }}
            </h3>
            <div class="space-y-3">
              <Link
                v-for="nearby in nearbyDestinations"
                :key="nearby.id"
                :href="`/destinations/${nearby.slug}`"
                class="flex items-center gap-3 group"
              >
                <img :src="nearby.image_url" :alt="nearby.name" class="w-12 h-12 rounded-xl object-cover" />
                <div class="flex-1 min-w-0">
                  <h4 class="text-xs font-bold text-text-main group-hover:text-primary truncate">
                    {{ nearby.name }}
                  </h4>
                  <span class="text-[11px] text-text-muted">
                    {{ nearby.ticket_price_domestic > 0 ? formatRupiah(nearby.ticket_price_domestic) : t('home.free') }}
                  </span>
                </div>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

