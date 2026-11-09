<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTrans } from '@/Composables/useTrans';

const { t } = useTrans();

const props = defineProps({
  destination: Object,
  nearbyDestinations: Array,
  relatedTours: Array,
});

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};
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
          <!-- Main Photo -->
          <div class="rounded-2xl overflow-hidden shadow-card border border-border h-80 sm:h-96 bg-slate-100">
            <img
              :src="destination.media[0]?.url || 'https://images.unsplash.com/photo-1578469550956-0e16b69c6a3d?auto=format&fit=crop&w=1200&q=80'"
              :alt="destination.name"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Header Info -->
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <span class="bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full">
                {{ destination.cluster_label }}
              </span>
              <span class="bg-secondary/10 text-secondary text-xs font-bold px-3 py-1 rounded-full">
                {{ destination.category || t('home.default_category') }}
              </span>
            </div>
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

