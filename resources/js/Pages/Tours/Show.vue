<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ItineraryMap from '@/Components/ItineraryMap.vue';
import { ref, computed } from 'vue';
import { useTrans } from '@/Composables/useTrans';
import { useFormat } from '@/Composables/useFormat';

const { t } = useTrans();
const { formatRupiah } = useFormat();

const props = defineProps({
  product: Object,
});

// Itinerary <-> Map sync state
const mapActiveDay = ref(null);
const activeStopId = ref(null);
const selectStopFromTimeline = (stop) => {
  if (!Number.isFinite(stop.latitude) || !Number.isFinite(stop.longitude)) return;
  activeStopId.value = null;
  requestAnimationFrame(() => (activeStopId.value = stop.id));
};
const onMapSelectStop = (stopId) => {
  activeStopId.value = stopId;
  document.getElementById(`stop-${stopId}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const activeMediaIndex = ref(0);

const mediaList = computed(() => {
  if (props.product?.media && props.product.media.length > 0) {
    return props.product.media;
  }
  return [
    {
      id: 'default-1',
      url: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
      caption: props.product?.name || 'Paket Wisata Sumatera Utara',
      location_name: 'Kaldera Danau Toba',
      location_slug: null,
    },
  ];
});

const currentMedia = computed(() => mediaList.value[activeMediaIndex.value] || mediaList.value[0]);

const participants = ref(props.product.tour.min_participants || 2);

const decrementParticipants = () => {
  if (participants.value > (props.product.tour?.min_participants || 1)) {
    participants.value--;
  }
};

const incrementParticipants = () => {
  if (participants.value < (props.product.tour?.max_participants || 50)) {
    participants.value++;
  }
};

const totalPrice = computed(() => {
  if (props.product.pricing_type === 'PER_GROUP') {
    return props.product.base_price;
  }
  return props.product.base_price * participants.value;
});

const dpAmount = computed(() => {
  return totalPrice.value * (props.product.dp_percentage / 100);
});

const remainingAmount = computed(() => {
  return totalPrice.value - dpAmount.value;
});

const isShareModalOpen = ref(false);
const copiedLink = ref(false);

const shareUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.href;
  }
  return `/tours/${props.product?.slug}`;
});

const shareText = computed(() => {
  return `Yuk liburan ke Danau Toba & Sumatera Utara bareng aku! Cek paket wisata "${props.product?.name}" di Beta Tudia - Muter-Muter: ${shareUrl.value}`;
});

const openShareModal = async () => {
  if (navigator.share) {
    try {
      await navigator.share({
        title: props.product?.name,
        text: `Cek paket wisata "${props.product?.name}" di Beta Tudia - Muter-Muter!`,
        url: shareUrl.value,
      });
      return;
    } catch (e) {
      // User cancelled or unsupported, fallback to modal
    }
  }
  isShareModalOpen.value = true;
};

const copyShareLink = async () => {
  try {
    await navigator.clipboard.writeText(shareUrl.value);
    copiedLink.value = true;
    setTimeout(() => {
      copiedLink.value = false;
    }, 2500);
  } catch (err) {
    // fallback
  }
};

const shareToWhatsApp = () => {
  const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText.value)}`;
  window.open(url, '_blank');
};

const shareToFacebook = () => {
  const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`;
  window.open(url, '_blank');
};

const shareToTwitter = () => {
  const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}`;
  window.open(url, '_blank');
};
</script>

<template>
  <AppLayout>
    <Head :title="`${product.name} — Paket Wisata Sumatera Utara`" />

    <!-- Breadcrumb -->
    <div class="bg-surface-muted border-b border-border py-4 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center gap-2 text-xs text-text-muted">
        <Link href="/" class="hover:text-primary">{{ t('nav.home') }}</Link>
        <span>/</span>
        <Link href="/tours" class="hover:text-primary">{{ t('nav.tour_packages') }}</Link>
        <span>/</span>
        <span class="text-text-main font-semibold truncate">{{ product.name }}</span>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Interactive Auto-Aggregated Destination Photo Gallery -->
          <div class="space-y-3">
            <div class="relative rounded-2xl overflow-hidden shadow-card border border-border h-80 sm:h-[420px] bg-slate-900 group">
              <img
                :src="currentMedia.url"
                :alt="currentMedia.caption || product.name"
                class="w-full h-full object-cover transition-all duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-transparent"></div>

              <!-- Top Counter Badge -->
              <div class="absolute top-4 right-4 bg-slate-900/80 backdrop-blur-md text-white px-3 py-1.5 rounded-full text-xs font-bold border border-white/15">
                📷 {{ activeMediaIndex + 1 }} / {{ mediaList.length }} Foto Rute & Tujuan
              </div>

              <!-- Bottom Location Binding Overlay -->
              <div class="absolute bottom-4 left-4 right-4 flex flex-col sm:flex-row sm:items-end justify-between gap-2 text-white">
                <div>
                  <span
                    v-if="currentMedia.location_name"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary/90 backdrop-blur-xs text-[11px] font-bold mb-1.5"
                  >
                    <span>📍 Lokasi di Foto: {{ currentMedia.location_name }}</span>
                    <span v-if="currentMedia.cluster_label" class="opacity-85">({{ currentMedia.cluster_label }})</span>
                  </span>
                  <p class="text-sm font-bold text-white drop-shadow-xs">
                    {{ currentMedia.caption || product.name }}
                  </p>
                </div>

                <Link
                  v-if="currentMedia.location_slug"
                  :href="`/destinations/${currentMedia.location_slug}`"
                  class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-white/95 hover:bg-white text-slate-900 text-xs font-bold shadow-md transition shrink-0"
                >
                  <span>Lihat Profil Destinasi</span>
                  <span>→</span>
                </Link>
              </div>
            </div>

            <!-- Thumbnail Carousel Strip -->
            <div v-if="mediaList.length > 1" class="flex items-center gap-2.5 overflow-x-auto pb-1">
              <button
                v-for="(item, idx) in mediaList"
                :key="item.id || idx"
                type="button"
                @click="activeMediaIndex = idx"
                class="relative rounded-xl overflow-hidden h-16 w-24 shrink-0 border-2 transition-all text-left group"
                :class="activeMediaIndex === idx ? 'border-primary ring-2 ring-primary/20 scale-[1.02]' : 'border-transparent opacity-70 hover:opacity-100'"
              >
                <img :src="item.url" :alt="item.location_name" class="w-full h-full object-cover" />
                <div class="absolute inset-x-0 bottom-0 bg-slate-900/75 px-1.5 py-0.5 text-[9px] font-bold text-white truncate">
                  {{ item.location_name || `Foto ${idx + 1}` }}
                </div>
              </button>
            </div>
          </div>

          <!-- Title, Badges & Reputation -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-3">
            <div class="flex flex-wrap items-center gap-2">
              <span
                v-if="product.ownership_type === 'INTERNAL'"
                class="bg-slate-900 text-white text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1.5"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                KURASI RESMI PLATFORM
              </span>
              <span class="bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full">
                {{ t('tours.days_nights', { days: product.tour.duration_days, nights: product.tour.duration_nights }) }}
              </span>
              <span class="bg-secondary/10 text-secondary text-xs font-bold px-3 py-1 rounded-full">
                {{ product.tour.tour_type }}
              </span>
              <span v-if="product.tour.includes_ferry" class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200 flex items-center gap-1">
                {{ t('tours.ferry_badge') }}
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-text-main tracking-tight">
              {{ product.name }}
            </h1>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100 text-xs">
              <div class="text-text-muted">
                {{ t('tours.provided_by') }}
                <span class="font-bold text-text-main">{{ product.partner.name }}</span>
                ({{ product.partner.city }})
              </div>
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200/70 text-amber-900 font-bold">
                <span>★ {{ product.partner.average_rating.toFixed(1) }}</span>
                <span class="text-amber-300">|</span>
                <span>{{ product.partner.completed_trips_count }} Perjalanan Sukses</span>
              </div>
            </div>

            <!-- Action Toolbar: Download PDF Itinerary & Share -->
            <div class="flex flex-wrap items-center gap-2.5 pt-3 border-t border-slate-100">
              <a
                :href="`/tours/${product.slug}/itinerary/print`"
                target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs hover:shadow-md transition cursor-pointer"
              >
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh Itinerary PDF (Beta Tudia)</span>
              </a>

              <button
                type="button"
                @click="openShareModal"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold border border-slate-300/70 shadow-xs transition cursor-pointer"
              >
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>
                <span>Bagikan Paket</span>
              </button>
            </div>
          </div>

          <!-- Description -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h2 class="text-base font-bold text-text-main mb-3">{{ t('tours.summary_title') }}</h2>
            <p class="text-sm text-text-muted leading-relaxed whitespace-pre-line">
              {{ product.full_description }}
            </p>
          </div>

          <!-- Itinerary Timeline with Bound Destination Photos -->
          <div v-if="product.itinerary && product.itinerary.length > 0" class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6 border-b border-slate-100 pb-4">
              <div>
                <h2 class="text-base font-bold text-text-main">{{ t('tours.itinerary_title') }}</h2>
                <p class="text-xs text-text-muted mt-0.5">
                  Dilengkapi foto visual lokasi tujuan wisata yang akan Anda kunjungi setiap hari
                </p>
              </div>
              <span class="text-[11px] font-bold text-primary bg-primary/10 px-3 py-1 rounded-full self-start sm:self-auto">
                Terhubung ke Katalog Destinasi Master
              </span>
            </div>

            <!-- Option A: split layout — timeline (left) + sticky interactive map (right) -->
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            <div class="xl:col-span-2 xl:order-2">
              <div class="xl:sticky xl:top-24 h-[360px] xl:h-[calc(100vh-8rem)] xl:max-h-[640px]">
                <ItineraryMap
                  :itinerary="product.itinerary"
                  v-model:active-day="mapActiveDay"
                  :active-stop-id="activeStopId"
                  @select-stop="onMapSelectStop"
                />
              </div>
            </div>

            <div class="xl:col-span-3 xl:order-1 space-y-8 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-border">
              <div v-for="day in product.itinerary" :key="day.id" class="relative pl-10">
                <!-- Day Badge Marker -->
                <button
                  type="button"
                  @click="mapActiveDay = day.day_number"
                  title="Tampilkan hari ini di peta"
                  class="absolute left-0 top-0 w-7 h-7 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center ring-4 ring-surface shadow-sm cursor-pointer"
                >
                  {{ day.day_number }}
                </button>

                <div>
                  <h3 class="text-base font-extrabold text-text-main">
                    {{ day.title }}
                  </h3>
                  <p v-if="day.description" class="text-xs text-text-muted mt-1 leading-relaxed">
                    {{ day.description }}
                  </p>

                  <!-- Stops with Bound Destination Photo & Link -->
                  <div v-if="day.stops && day.stops.length > 0" class="mt-4 space-y-3">
                    <div
                      v-for="stop in day.stops"
                      :key="stop.id"
                      :id="`stop-${stop.id}`"
                      @click="selectStopFromTimeline(stop)"
                      class="p-4 rounded-2xl border transition flex flex-col sm:flex-row items-start justify-between gap-4 text-xs"
                      :class="[
                        activeStopId === stop.id ? 'bg-primary/5 border-primary ring-2 ring-primary/20' : 'bg-surface-muted border-border hover:border-primary/40',
                        Number.isFinite(stop.latitude) ? 'cursor-pointer' : '',
                      ]"
                    >
                      <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        <!-- Bound Destination Photo Thumbnail -->
                        <Link
                          v-if="stop.destination_image_url"
                          :href="stop.destination_slug ? `/destinations/${stop.destination_slug}` : '#'"
                          class="relative w-20 h-20 sm:w-24 sm:h-20 rounded-xl overflow-hidden shrink-0 border border-slate-200 group"
                        >
                          <img
                            :src="stop.destination_image_url"
                            :alt="stop.destination_name || stop.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                          />
                        </Link>

                        <div class="min-w-0 flex-1">
                          <div class="flex flex-wrap items-center gap-2">
                            <span class="font-extrabold text-text-main text-sm">{{ stop.title }}</span>
                            <span
                              v-if="stop.destination_cluster"
                              class="px-2 py-0.5 rounded-md bg-primary/10 text-primary text-[10px] font-bold"
                            >
                              {{ stop.destination_cluster }}
                            </span>
                          </div>
                          <p v-if="stop.activity_description" class="text-text-muted mt-1 text-xs leading-relaxed">
                            {{ stop.activity_description }}
                          </p>
                          <div v-if="stop.destination_slug" class="mt-2">
                            <Link
                              :href="`/destinations/${stop.destination_slug}`"
                              class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline"
                            >
                              <span>Lihat Foto & Detail Destinasi {{ stop.destination_name }}</span>
                              <span>→</span>
                            </Link>
                          </div>
                        </div>
                      </div>

                      <div class="flex sm:flex-col items-end justify-between shrink-0 gap-1 self-stretch sm:self-auto">
                        <span v-if="stop.estimated_minutes" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 whitespace-nowrap text-[11px] font-semibold">
                          ⏱️ ~{{ stop.estimated_minutes }} menit
                        </span>
                        <span v-if="Number.isFinite(stop.latitude)" class="text-[10px] font-bold text-primary whitespace-nowrap">📍 Lihat di peta</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
          </div>

          <!-- Inclusions & Exclusions -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
              <h3 class="text-sm font-bold text-emerald-700 mb-3 flex items-center gap-1.5">
                <span>✅</span>
                <span>{{ t('tours.inclusions_title') }}</span>
              </h3>
              <p class="text-xs text-text-muted whitespace-pre-line leading-relaxed">
                {{ product.tour.inclusions || '• Armada Bersih & AC Dingin\n• BBM Pulang-Pergi\n• Driver merangkap Guide Lokal\n• Makan & Uang Rokok Supir\n• Tiket Ferry Danau Toba PP' }}
              </p>
            </div>

            <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
              <h3 class="text-sm font-bold text-rose-700 mb-3 flex items-center gap-1.5">
                <span>❌</span>
                <span>{{ t('tours.exclusions_title') }}</span>
              </h3>
              <p class="text-xs text-text-muted whitespace-pre-line leading-relaxed">
                {{ product.tour.exclusions || 'Tiket pesawat menuju Bandara Kualanamu/Silangit, pengeluaran pribadi.' }}
              </p>
            </div>
          </div>

          <!-- Platform Win-Win Guarantee Box -->
          <div class="bg-emerald-950 text-white p-6 rounded-2xl border border-emerald-800 shadow-card space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-300">Jaminan Ekosistem Resmi Muter-Muter</span>
                <h3 class="text-base font-extrabold mt-0.5">Perlindungan Penuh Wisatawan & Standar Armada Bersih</h3>
              </div>
              <span class="px-2.5 py-1 rounded-full bg-emerald-800 text-emerald-200 text-[10px] font-bold">100% Terlindungi</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-emerald-100">
              <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                <div class="font-bold text-white">🚐 Standar Armada Bersih & Harum</div>
                <p class="text-[11px] text-emerald-200/90 mt-0.5">Kabin divakum sebelum penjemputan, AC dingin merata, bebas bau asap rokok.</p>
              </div>
              <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                <div class="font-bold text-white">🛠️ Garansi Unit Pengganti 1–2 Jam</div>
                <p class="text-[11px] text-emerald-200/90 mt-0.5">Jika terjadi kendala teknis di rute kaldera/gunung, mobil pengganti langsung dikirim dari mitra terdekat.</p>
              </div>
              <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                <div class="font-bold text-white">☕ Include Makan & Rokok Supir</div>
                <p class="text-[11px] text-emerald-200/90 mt-0.5">Bebas rasa canggung dan bebas pungutan tips terselubung selama perjalanan.</p>
              </div>
              <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                <div class="font-bold text-white">🔒 Kontak Supir Rilis H-1 (Sinkronisasi KNO)</div>
                <p class="text-[11px] text-emerald-200/90 mt-0.5">Privasi terjaga; nomor WhatsApp supir & plat mobil dikirim otomatis H-1 untuk penjemputan bandara.</p>
              </div>
            </div>
          </div>

          <!-- Cancellation Policy Snapshot Info -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h3 class="text-sm font-bold text-text-main mb-2">{{ t('tours.cancellation_title') }}</h3>
            <div class="grid grid-cols-3 gap-2 text-center text-xs mt-3">
              <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                <span class="font-bold text-emerald-800 block">> 7 Days</span>
                <span class="text-[11px] text-emerald-600">{{ t('tours.refund_100') }}</span>
              </div>
              <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                <span class="font-bold text-amber-800 block">3 - 7 Days</span>
                <span class="text-[11px] text-amber-600">{{ t('tours.refund_50') }}</span>
              </div>
              <div class="p-3 rounded-xl bg-rose-50 border border-rose-200">
                <span class="font-bold text-rose-800 block">&lt; 3 Days</span>
                <span class="text-[11px] text-rose-600">{{ t('tours.refund_0') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky Booking Card (1 Col) -->
        <div>
          <div class="sticky top-20 bg-surface p-6 rounded-2xl border border-border shadow-xl space-y-5">
            <div>
              <span class="text-xs text-text-muted uppercase font-bold block">{{ t('home.starting_from') }}</span>
              <div class="text-2xl font-black text-primary tabular-nums">
                {{ formatRupiah(product.base_price) }}
                <span class="text-xs font-normal text-text-muted">
                  {{ product.pricing_type === 'PER_GROUP' ? '/ Rombongan (Unit)' : t('home.per_person') }}
                </span>
              </div>
            </div>

            <!-- Participant Counter -->
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase mb-1.5">{{ t('tours.participants_label') }}</label>
              <div class="flex items-center gap-3">
                <button
                  @click="decrementParticipants"
                  class="w-10 h-10 rounded-xl border border-border hover:bg-surface-muted font-bold text-lg flex items-center justify-center"
                >
                  -
                </button>
                <span class="flex-1 text-center font-bold text-base text-text-main">{{ participants }} {{ t('home.persons') }}</span>
                <button
                  @click="incrementParticipants"
                  class="w-10 h-10 rounded-xl border border-border hover:bg-surface-muted font-bold text-lg flex items-center justify-center"
                >
                  +
                </button>
              </div>
              <span class="text-[11px] text-text-muted mt-1 block">
                {{ t('tours.capacity_note', { min: product.tour.min_participants, max: product.tour.max_participants }) }}
              </span>
            </div>

            <!-- Price Breakdown -->
            <div class="p-4 bg-surface-muted rounded-xl border border-border space-y-2 text-xs tabular-nums">
              <div class="flex justify-between">
                <span class="text-text-muted">{{ t('tours.total_estimate') }}</span>
                <span class="font-bold text-text-main">{{ formatRupiah(totalPrice) }}</span>
              </div>
              <div class="flex justify-between text-emerald-700 font-bold pt-2 border-t border-border">
                <span>{{ t('tours.required_dp', { dp: product.dp_percentage }) }}</span>
                <span>{{ formatRupiah(dpAmount) }}</span>
              </div>
              <div class="flex justify-between text-text-muted text-[11px]">
                <span>{{ t('tours.remaining_payment') }}</span>
                <span>{{ formatRupiah(remainingAmount) }}</span>
              </div>
            </div>

            <!-- SLA & Lead time notice -->
            <div class="text-[11px] text-text-muted space-y-1.5">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Termasuk <strong>Garansi Mobil Pengganti 1–2 Jam</strong></span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                <span>Armada Bersih, AC Dingin & Include Makan/Rokok Supir</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Batas booking minimal: <strong class="text-text-main">H-1 (24 Jam)</strong></span>
              </div>
            </div>

            <!-- CTA Button -->
            <Link
              :href="`/checkout?product_id=${product.id}&participants=${participants}`"
              class="w-full bg-accent hover:bg-accent-hover text-white font-bold py-3.5 px-4 rounded-xl text-center text-sm transition-colors shadow-lg block"
            >
              {{ t('tours.book_now_btn') }}
            </Link>

            <!-- Secondary Actions: Quick PDF & Share -->
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-center">
              <a
                :href="`/tours/${product.slug}/itinerary/print`"
                target="_blank"
                class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <span>📄</span>
                <span>Unduh PDF</span>
              </a>
              <button
                type="button"
                @click="openShareModal"
                class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <span>📤</span>
                <span>Bagikan</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Share Tour Modal (Beta Tudia Experience) -->
    <div
      v-if="isShareModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      @click.self="isShareModalOpen = false"
    >
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-start justify-between">
          <div>
            <span class="text-[10px] font-black uppercase tracking-widest text-primary">Beta Tudia • Bagikan Perjalanan</span>
            <h3 class="text-lg font-black text-slate-900 mt-0.5">Ajak Teman & Keluarga Liburan</h3>
          </div>
          <button
            type="button"
            @click="isShareModalOpen = false"
            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition cursor-pointer"
          >
            ✕
          </button>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
          Bagikan rute terkurasi <strong>{{ product.name }}</strong> langsung ke kontak WhatsApp atau sosial media Anda:
        </p>

        <!-- Direct WhatsApp Share Button (Hero Option) -->
        <button
          type="button"
          @click="shareToWhatsApp"
          class="w-full flex items-center justify-center gap-2.5 py-3 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md hover:shadow-lg transition cursor-pointer"
        >
          <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.693.076-2.072-.495-1.769-.733-2.906-2.535-2.994-2.651-.088-.116-.719-.958-.719-1.826 0-.868.455-1.294.618-1.469.163-.175.356-.219.474-.219.119 0 .237.001.341.006.11.005.257-.042.401.306.149.36.509 1.242.553 1.332.044.09.074.195.015.313-.06.118-.089.192-.178.297-.089.105-.187.235-.267.316-.089.09-.182.188-.078.367.104.179.463.766.995 1.241.684.611 1.261.801 1.44.89.179.089.284.075.39-.045.105-.12.446-.521.565-.7.119-.179.238-.149.401-.089.163.059 1.036.489 1.214.578.178.089.297.134.341.209.045.074.045.433-.099.838z"/>
          </svg>
          <span>Bagikan ke WhatsApp Chat / Group</span>
        </button>

        <!-- Social Share Row -->
        <div class="grid grid-cols-2 gap-2 text-xs">
          <button
            type="button"
            @click="shareToFacebook"
            class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold border border-blue-200 transition cursor-pointer"
          >
            <span>Facebook</span>
          </button>
          <button
            type="button"
            @click="shareToTwitter"
            class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold border border-slate-300 transition cursor-pointer"
          >
            <span>X / Twitter</span>
          </button>
        </div>

        <!-- Copy Link Section -->
        <div class="space-y-1.5 pt-2 border-t border-slate-100">
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tautan Paket</label>
          <div class="flex items-center gap-2">
            <input
              type="text"
              readonly
              :value="shareUrl"
              class="w-full bg-slate-50 text-slate-700 text-xs px-3 py-2 rounded-xl border border-slate-200 select-all font-mono"
            />
            <button
              type="button"
              @click="copyShareLink"
              class="shrink-0 px-4 py-2 rounded-xl text-xs font-extrabold transition cursor-pointer"
              :class="copiedLink ? 'bg-emerald-600 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white'"
            >
              {{ copiedLink ? 'Disalin! ✓' : 'Salin' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
