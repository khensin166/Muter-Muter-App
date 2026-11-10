<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { t, locale } = useTrans();

const props = defineProps({
  clusters: {
    type: Array,
    default: () => [],
  },
  featuredDestinations: {
    type: Array,
    default: () => [],
  },
  featuredTours: {
    type: Array,
    default: () => [],
  },
  featuredVehicles: {
    type: Array,
    default: () => [],
  },
});

const activeSearchTab = ref('tour');
const selectedCluster = ref('');


const clusterIcons = {
  PARAPAT: '🚢',
  SAMOSIR_EAST: '🏛️',
  SAMOSIR_WEST: '⛰️',
  MEREK_TONGGING: '🌲',
};
</script>

<template>
  <AppLayout>

    <Head :title="t('home.page_title')" />

    <!-- Hero Section -->
    <section
      class="relative bg-gradient-to-b from-primary-dark via-primary to-primary-light text-white py-16 md:py-24 overflow-hidden">
      <!-- Caldera contour background decorative SVG -->
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full object-cover" viewBox="0 0 100 100" preserveAspectRatio="none">
          <path d="M0,0 C30,40 70,20 100,50 L100,100 L0,100 Z" fill="currentColor" />
        </svg>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <div
          class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-md px-3.5 py-1.5 rounded-full text-xs font-semibold mb-6 border border-white/20">
          <span>✨</span>
          <span>{{ t('home.hero_badge') }}</span>
        </div>

        <h1 class="text-3xl sm:text-5xl md:text-6xl font-black tracking-tight max-w-4xl mx-auto leading-tight">
          {{ t('home.hero_title_prefix') }}
          <span class="text-accent underline decoration-accent/40 underline-offset-8">{{ t('home.hero_title_highlight')
          }}</span>
        </h1>

        <p class="mt-6 text-base sm:text-lg md:text-xl text-slate-200 max-w-2xl mx-auto font-light leading-relaxed">
          {{ t('home.hero_subtitle') }}
        </p>

        <!-- Search / Filter Card -->
        <div
          class="mt-10 max-w-4xl mx-auto bg-surface rounded-2xl p-4 sm:p-6 shadow-2xl border border-border text-left text-text-main">
          <div class="flex items-center gap-2 border-b border-border pb-3 mb-4 text-xs sm:text-sm font-bold">
            <button @click="activeSearchTab = 'tour'"
              :class="activeSearchTab === 'tour' ? 'bg-primary text-white' : 'hover:bg-surface-muted text-text-muted'"
              class="px-4 py-2 rounded-xl transition">
              {{ t('home.tab_tours') }}
            </button>
            <button @click="activeSearchTab = 'vehicle'"
              :class="activeSearchTab === 'vehicle' ? 'bg-primary text-white' : 'hover:bg-surface-muted text-text-muted'"
              class="px-4 py-2 rounded-xl transition">
              {{ t('home.tab_vehicles') }}
            </button>
            <Link href="/destinations"
              class="px-4 py-2 rounded-xl hover:bg-surface-muted text-text-muted transition inline-flex items-center gap-1.5">
              {{ t('home.tab_destinations') }}
            </Link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">{{
                t('home.filter_cluster_label') }}</label>
              <select v-model="selectedCluster"
                class="w-full text-sm font-medium border-border rounded-xl focus:border-primary focus:ring-primary bg-background">
                <option value="">{{ t('home.filter_all_regions') }}</option>
                <option v-for="c in clusters" :key="c.value" :value="c.value">
                  {{ c.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">{{
                t('home.filter_lead_time_label') }}</label>
              <div
                class="w-full py-2.5 px-3 text-xs font-semibold bg-surface-muted rounded-xl text-text-muted border border-border">
                {{ t('home.filter_lead_time_value') }}
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">{{
                t('home.filter_payment_label') }}</label>
              <div
                class="w-full py-2.5 px-3 text-xs font-semibold bg-surface-muted rounded-xl text-primary border border-border">
                {{ t('home.filter_payment_value') }}
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-border flex flex-col sm:flex-row justify-between items-center gap-3">
            <span class="text-xs text-text-muted flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-secondary"></span>
              {{ t('home.filter_guarantee_note') }}
            </span>
            <Link
              :href="activeSearchTab === 'tour' ? `/tours${selectedCluster ? '?cluster=' + selectedCluster : ''}` : `/vehicles`"
              class="w-full sm:w-auto bg-accent hover:bg-accent-hover text-white text-sm font-bold py-2.5 px-6 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
              <span>🔍</span>
              <span>{{ t('home.btn_view_catalog') }}</span>
            </Link>
          </div>
        </div>

        <!-- Quick navigation to Portals -->
        <div class="mt-8 flex flex-wrap justify-center items-center gap-3 text-xs">
          <span class="text-slate-300">{{ t('home.quick_access') }}</span>
          <Link href="/destinations"
            class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium transition">
            {{ t('home.quick_destinations') }}
          </Link>
          <Link href="/partner"
            class="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 font-semibold transition">
            {{ t('home.quick_partner') }}
          </Link>
          <Link href="/admin"
            class="px-3 py-1.5 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 text-purple-200 border border-purple-400/30 font-semibold transition">
            {{ t('home.quick_admin') }}
          </Link>
        </div>
      </div>
    </section>

    <!-- Geographic Clusters Section -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-widest">{{ t('home.clusters_eyebrow') }}</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">{{ t('home.clusters_title') }}</h2>
        </div>
        <p class="text-sm text-text-muted mt-2 md:mt-0 max-w-md">
          {{ t('home.clusters_subtitle') }}
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <Link v-for="cluster in clusters" :key="cluster.value" :href="`/destinations?cluster=${cluster.value}`"
          class="bg-surface rounded-2xl p-6 border border-border hover:border-primary/40 hover:shadow-card-hover transition-all group flex flex-col justify-between cursor-pointer">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-3xl">{{ clusterIcons[cluster.value] || '📍' }}</span>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-secondary-light text-secondary">
                {{ t('home.cluster_active') }}
              </span>
            </div>
            <h3 class="text-lg font-bold text-text-main group-hover:text-primary transition-colors">
              {{ cluster.label }}
            </h3>
            <p class="text-xs text-text-muted mt-2 leading-relaxed">
              {{ t('home.cluster_card_desc', { cluster: cluster.label }) }}
            </p>
          </div>
          <div
            class="mt-6 pt-3 border-t border-border flex items-center justify-between text-xs font-bold text-primary">
            <span>{{ t('home.cluster_view_spots') }}</span>
            <span class="group-hover:translate-x-1 transition-transform">{{ t('home.cluster_explore') }}</span>
          </div>
        </Link>
      </div>
    </section>

    <!-- Featured Destinations Section -->
    <section class="py-12 bg-surface-muted border-y border-border">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
          <div>
            <span class="text-xs font-bold text-secondary uppercase tracking-widest">{{ t('home.destinations_eyebrow')
            }}</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">{{ t('home.destinations_title') }}</h2>
          </div>
          <Link href="/destinations"
            class="text-sm font-bold text-primary hover:text-primary-dark mt-2 md:mt-0 flex items-center gap-1">
            {{ t('home.destinations_view_all') }}
          </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link v-for="dest in featuredDestinations" :key="dest.id" :href="`/destinations/${dest.slug}`"
            class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover hover:border-primary/30 transition-all flex flex-col group">
            <div class="relative h-48 overflow-hidden bg-slate-100">
              <img :src="dest.image_url" :alt="dest.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              <div
                class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-primary shadow-sm">
                {{ dest.cluster_label }}
              </div>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <span class="text-[11px] font-semibold text-secondary uppercase">{{ dest.category ||
                  t('home.default_category') }}</span>
                <h3 class="text-base font-bold text-text-main mt-1 group-hover:text-primary transition-colors">
                  {{ dest.name }}
                </h3>
              </div>
              <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs">
                <span class="text-text-muted">{{ t('home.entry_ticket') }}</span>
                <span class="font-bold text-primary">
                  {{ dest.ticket_price_domestic > 0 ? formatRupiah(dest.ticket_price_domestic) : t('home.free') }}
                </span>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Tour Packages Section -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-widest">{{ t('home.tours_eyebrow') }}</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">{{ t('home.tours_title') }}</h2>
          <p class="text-sm text-text-muted mt-1">{{ t('home.tours_subtitle') }}</p>
        </div>
        <Link href="/tours"
          class="text-sm font-bold text-primary hover:text-primary-dark mt-2 md:mt-0 flex items-center gap-1">
          {{ t('home.tours_view_all') }}
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="tour in featuredTours" :key="tour.id"
          class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover hover:border-primary/30 transition-all flex flex-col">
          <div class="relative h-48 overflow-hidden bg-slate-200">
            <img :src="tour.image_url" :alt="tour.name" class="w-full h-full object-cover" />
            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
              <span class="bg-primary/90 text-white text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur-sm">
                {{ tour.duration_days }}{{ locale === 'en' ? 'D' : 'H' }}{{ tour.duration_nights }}{{ locale === 'en' ?
                  'N' : 'M' }}
              </span>
              <span v-if="tour.includes_ferry"
                class="bg-secondary/90 text-white text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur-sm flex items-center gap-1">
                {{ t('home.ferry_included') }}
              </span>
            </div>
          </div>

          <div class="p-6 flex-1 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between text-xs text-text-muted mb-2">
                <span>{{ tour.tour_type }}</span>
                <span class="text-secondary font-semibold">{{ t('home.min_days_lead', {
                  days: tour.min_lead_time_hours /
                    24
                }) }}</span>
              </div>
              <h3 class="text-lg font-bold text-text-main leading-snug hover:text-primary transition-colors">
                <Link :href="`/tours/${tour.slug}`">{{ tour.name }}</Link>
              </h3>
              <p class="text-xs text-text-muted mt-2 line-clamp-2">
                {{ tour.short_description }}
              </p>
            </div>

            <div class="mt-6 pt-4 border-t border-border">
              <div class="flex items-baseline justify-between">
                <div>
                  <span class="text-[10px] uppercase font-bold text-text-muted">{{ t('home.starting_from') }}</span>
                  <div class="text-xl font-black text-primary">
                    {{ formatRupiah(tour.base_price) }}
                    <span class="text-xs font-normal text-text-muted">{{ t('home.per_person') }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-[10px] uppercase font-bold text-emerald-600 block">{{ t('home.dp_only', {
                    dp:
                      tour.dp_percentage
                  }) }}</span>
                  <span class="text-xs font-bold text-slate-700">{{ formatRupiah(tour.base_price * (tour.dp_percentage /
                    100)) }}</span>
                </div>
              </div>

              <Link :href="`/tours/${tour.slug}`"
                class="mt-4 w-full bg-primary hover:bg-primary-dark text-white font-bold py-2.5 px-4 rounded-xl text-center text-sm transition-colors block">
                {{ t('home.tour_detail_btn') }}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Vehicle Rentals Section -->
    <section class="py-16 bg-surface-muted border-t border-border">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
          <div>
            <span class="text-xs font-bold text-secondary uppercase tracking-widest">{{ t('home.vehicles_eyebrow')
            }}</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">{{ t('home.vehicles_title') }}</h2>
            <p class="text-sm text-text-muted mt-1">{{ t('home.vehicles_subtitle') }}</p>
          </div>
          <Link href="/vehicles"
            class="text-sm font-bold text-primary hover:text-primary-dark mt-2 md:mt-0 flex items-center gap-1">
            {{ t('home.vehicles_view_all') }}
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="veh in featuredVehicles" :key="veh.id"
            class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover transition-all flex flex-col">
            <div class="relative h-44 overflow-hidden bg-slate-100">
              <img :src="veh.image_url" :alt="veh.name" class="w-full h-full object-cover" />
              <div
                class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-slate-800 shadow-sm">
                {{ t('home.min_hours_lead', { hours: veh.min_lead_time_hours }) }}
              </div>
            </div>

            <div class="p-6 flex-1 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between text-xs text-text-muted mb-1">
                  <span>{{ veh.brand }} {{ veh.year }}</span>
                  <span class="font-semibold text-emerald-600">{{ veh.with_driver ? t('home.with_driver') :
                    t('home.self_drive') }}</span>
                </div>
                <h3 class="text-lg font-bold text-text-main">
                  <Link :href="`/vehicles/${veh.slug}`">{{ veh.name }}</Link>
                </h3>

                <div
                  class="mt-3 flex items-center gap-4 text-xs text-text-muted font-medium bg-surface-muted p-2.5 rounded-xl border border-border">
                  <div class="flex items-center gap-1">
                    <span>👥</span>
                    <span>{{ veh.passenger_capacity }} {{ t('home.persons') }}</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span>🧳</span>
                    <span>{{ veh.luggage_capacity }} {{ t('home.suitcases') }}</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span>⚙️</span>
                    <span>{{ veh.transmission }}</span>
                  </div>
                </div>
              </div>

              <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                <div>
                  <span class="text-[10px] uppercase font-bold text-text-muted">{{ t('home.rental_rate') }}</span>
                  <div class="text-xl font-black text-primary">
                    {{ formatRupiah(veh.base_price) }}
                    <span class="text-xs font-normal text-text-muted">{{ t('home.per_day') }}</span>
                  </div>
                </div>
                <Link :href="`/vehicles/${veh.slug}`"
                  class="bg-accent hover:bg-accent-hover text-white font-bold py-2 px-4 rounded-xl text-xs transition-colors shadow-sm">
                  {{ t('home.rent_unit_btn') }}
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5 Golden Guarantees & Mutual Benefit Ecosystem Agreement -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
      <div class="text-center max-w-3xl mx-auto">
        <span
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-widest mb-2">
          <span>🛡️ Standar Emas & Jaminan Resmi Muter-Muter</span>
        </span>
        <h2 class="text-2xl sm:text-4xl font-extrabold text-text-main tracking-tight">
          {{ locale === 'en' ? '5 Golden Standards & Win-Win Platform Guarantees' : '5 Standar Emas Kenyamanan & Jaminan Keuntungan Bersama' }}
        </h2>
        <p class="text-sm text-text-muted mt-2 leading-relaxed">
          {{
            locale === 'en'
              ? 'We guarantee clean vehicles, emergency backup cars in mountain routes, all-inclusive driver meals, and fair 90% partner earnings.'
              : 'Komitmen resmi kami untuk memastikan wisatawan berlibur dengan tenang, armada selalu bersih dan harum, serta mitra pengemudi lokal sejahtera.'
          }}
        </p>
      </div>

      <!-- 5 Pillar Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- 1. Clean Vehicle Standard -->
        <div
          class="p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs hover:shadow-md transition flex flex-col justify-between">
          <div>
            <div
              class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-700 flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
              </svg>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm">
              {{ locale === 'en' ? 'Clean & Fresh Vehicle Standard' : 'Jaminan Kendaraan Bersih & Harum' }}
            </h4>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
              {{
                locale === 'en'
                  ? 'Vacuumed cabin before every pickup, cold AC across all rows, smoke-free interior, and mountain-ready brakes.'
                  : 'Kabin wajib divakum sebelum penjemputan, AC dingin merata, bebas bau asap rokok, dan rem/ban prima untuk rute kaldera.'
              }}
            </p>
          </div>
          <span class="mt-4 pt-2.5 border-t border-slate-100 text-[10px] font-bold text-emerald-700 block">
            ✓ Inspeksi Kebersihan Wajib
          </span>
        </div>

        <!-- 2. Emergency Replacement 1-2 Hours -->
        <div
          class="p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs hover:shadow-md transition flex flex-col justify-between">
          <div>
            <div
              class="w-11 h-11 rounded-xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
              </svg>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm">
              {{ locale === 'en' ? '1–2 Hour Replacement Guarantee' : 'Garansi Unit Pengganti 1–2 Jam' }}
            </h4>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
              {{
                locale === 'en'
                  ? 'If any mechanical issue occurs on steep Lake Toba or Bukit Lawang routes, we dispatch a backup vehicle within 1–2 hours.'
                  : 'Jika ada kendala mesin/ban di jalur terjal Tele, Sibea-bea, atau Bukit Lawang, mobil pengganti langsung dikirim dalam 1–2 jam.'
              }}
            </p>
          </div>
          <span class="mt-4 pt-2.5 border-t border-slate-100 text-[10px] font-bold text-primary block">
            ✓ Jaringan Rescue Se-Sumut
          </span>
        </div>

        <!-- 3. Driver Meals & Cigarettes Included -->
        <div
          class="p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs hover:shadow-md transition flex flex-col justify-between">
          <div>
            <div
              class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-700 flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
              </svg>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm">
              {{ locale === 'en' ? 'Driver Meals & Perks Included' : 'Include Makan & Rokok Supir' }}
            </h4>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
              {{
                locale === 'en'
                  ? 'Friendly local driver-guide with meals & daily allowance already included—zero awkward tipping or hidden fees.'
                  : 'Supir merangkap guide lokal ramah; biaya makan & uang rokok supir sudah termasuk tanpa pungli atau canggung di jalan.'
              }}
            </p>
          </div>
          <span class="mt-4 pt-2.5 border-t border-slate-100 text-[10px] font-bold text-amber-700 block">
            ✓ Bebas Biaya Tersembunyi
          </span>
        </div>

        <!-- 4. Scheduled Ferry Tickets -->
        <div
          class="p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs hover:shadow-md transition flex flex-col justify-between">
          <div>
            <div
              class="w-11 h-11 rounded-xl bg-sky-50 border border-sky-200/60 text-sky-700 flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
              </svg>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm">
              {{ t('home.why_4_title') }}
            </h4>
            <p class="text-xs text-text-muted mt-2 leading-relaxed">
              {{ t('home.why_4_desc') }}
            </p>
          </div>
          <span class="mt-4 pt-2.5 border-t border-slate-100 text-[10px] font-bold text-sky-700 block">
            ✓ Jaminan Tiket PP Samosir
          </span>
        </div>

        <!-- 5. Transparent 30% DP & H-1 Contact Sync -->
        <div
          class="p-5 rounded-2xl border border-slate-200/80 bg-white shadow-2xs hover:shadow-md transition flex flex-col justify-between">
          <div>
            <div
              class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-200/60 text-indigo-700 flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
              </svg>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm">
              {{ t('home.why_2_title') }}
            </h4>
            <p class="text-xs text-text-muted mt-2 leading-relaxed">
              {{ t('home.why_2_desc') }}
            </p>
          </div>
          <span class="mt-4 pt-2.5 border-t border-slate-100 text-[10px] font-bold text-indigo-700 block">
            ✓ Invoice Resmi & Hold 24 Jam
          </span>
        </div>
      </div>

      <!-- Perjanjian Keuntungan Bersama (Win-Win Ecosystem Pact Banner) -->
      <div class="rounded-3xl bg-slate-900 text-white p-6 sm:p-10 shadow-xl border border-slate-800">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          <div class="lg:col-span-5 space-y-3">
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[11px] font-bold">
              🤝 Perjanjian Ekosistem Saling Menguntungkan
            </span>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight leading-snug">
              Mengapa Memesan Resmi di Platform Jauh Lebih Menguntungkan?
            </h3>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
              Kami membangun ekosistem yang adil tanpa merugikan siapa pun. Wisatawan mendapatkan garansi penuh atas
              keselamatan & kenyamanan armada, sementara mitra pengemudi lokal menerima bagi hasil transparan dan
              perlindungan kerja.
            </p>
          </div>

          <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2.5">
              <div class="font-extrabold text-emerald-300 text-sm flex items-center gap-2">
                <span>🛡️</span>
                <span>Jaminan Hak Wisatawan</span>
              </div>
              <ul class="space-y-2 text-slate-200 text-[11px]">
                <li class="flex items-start gap-2">
                  <span class="text-emerald-400 font-bold">✓</span>
                  <span><strong>Mobil Bersih & Wangi:</strong> Bebas bau rokok, AC dingin, dan higienis.</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-emerald-400 font-bold">✓</span>
                  <span><strong>Rescue 1–2 Jam:</strong> Jaminan mobil cadangan jika mogok di tanjakan kaldera.</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-emerald-400 font-bold">✓</span>
                  <span><strong>Harga Transparan All-In:</strong> Sudah termasuk BBM, makan & rokok supir.</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-emerald-400 font-bold">✓</span>
                  <span><strong>Privasi & Sinkronisasi KNO:</strong> Kontak supir rilis otomatis H-1
                    keberangkatan.</span>
                </li>
              </ul>
            </div>

            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2.5">
              <div class="font-extrabold text-amber-300 text-sm flex items-center gap-2">
                <span>🚐</span>
                <span>Kesejahteraan Mitra & Supir</span>
              </div>
              <ul class="space-y-2 text-slate-200 text-[11px]">
                <li class="flex items-start gap-2">
                  <span class="text-amber-400 font-bold">✓</span>
                  <span><strong>Bagi Hasil Adil:</strong> Potongan platform sangat ringan & wajar.</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-amber-400 font-bold">✓</span>
                  <span><strong>Priority Dispatch:</strong> Supir berating tinggi diprioritaskan untuk order Famtrip &
                    grup besar.</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-amber-400 font-bold">✓</span>
                  <span><strong>Proteksi Pembatalan:</strong> Jaminan kompensasi DP jika tamu batal mendadak (&lt;3
                    hari).</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-amber-400 font-bold">✓</span>
                  <span><strong>Bebas Perang Harga Liar:</strong> Uang makan & operasional supir sudah dijamin
                    penuh.</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
