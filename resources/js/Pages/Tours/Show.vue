<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
  product: Object,
});

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
  return props.product.base_price * participants.value;
});

const dpAmount = computed(() => {
  return totalPrice.value * (props.product.dp_percentage / 100);
});

const remainingAmount = computed(() => {
  return totalPrice.value - dpAmount.value;
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
    <Head :title="`${product.name} — Paket Tour Danau Toba`" />

    <!-- Breadcrumb -->
    <div class="bg-surface-muted border-b border-border py-4 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center gap-2 text-xs text-text-muted">
        <Link href="/" class="hover:text-primary">Beranda</Link>
        <span>/</span>
        <Link href="/tours" class="hover:text-primary">Paket Wisata</Link>
        <span>/</span>
        <span class="text-text-main font-semibold truncate">{{ product.name }}</span>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Main Photo -->
          <div class="rounded-2xl overflow-hidden shadow-card border border-border h-80 sm:h-96 bg-slate-100">
            <img
              :src="product.media[0]?.url || 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80'"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Title & Badges -->
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <span class="bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full">
                {{ product.tour.duration_days }} Hari {{ product.tour.duration_nights }} Malam
              </span>
              <span class="bg-secondary/10 text-secondary text-xs font-bold px-3 py-1 rounded-full">
                {{ product.tour.tour_type }}
              </span>
              <span v-if="product.tour.includes_ferry" class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200 flex items-center gap-1">
                🚢 Tiket Ferry Danau Toba Termasuk
              </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-text-main">
              {{ product.name }}
            </h1>
            <p class="text-xs text-text-muted mt-2">
              Disediakan oleh mitra resmi: <span class="font-bold text-text-main">{{ product.partner.name }}</span> ({{ product.partner.city }})
            </p>
          </div>

          <!-- Description -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h2 class="text-base font-bold text-text-main mb-3">Ringkasan Perjalanan</h2>
            <p class="text-sm text-text-muted leading-relaxed whitespace-pre-line">
              {{ product.full_description }}
            </p>
          </div>

          <!-- Itinerary Timeline -->
          <div v-if="product.itinerary && product.itinerary.length > 0" class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h2 class="text-base font-bold text-text-main mb-6">Jadwal & Rencana Perjalanan Harian</h2>

            <div class="space-y-8 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-border">
              <div v-for="day in product.itinerary" :key="day.id" class="relative pl-10">
                <!-- Day Badge Marker -->
                <div class="absolute left-0 top-0 w-7 h-7 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center ring-4 ring-surface shadow-sm">
                  {{ day.day_number }}
                </div>

                <div>
                  <h3 class="text-base font-bold text-text-main">
                    Hari {{ day.day_number }}: {{ day.title }}
                  </h3>
                  <p v-if="day.description" class="text-xs text-text-muted mt-1">
                    {{ day.description }}
                  </p>

                  <!-- Stops -->
                  <div v-if="day.stops && day.stops.length > 0" class="mt-4 space-y-2.5">
                    <div
                      v-for="stop in day.stops"
                      :key="stop.id"
                      class="p-3 bg-surface-muted rounded-xl border border-border flex items-start justify-between gap-3 text-xs"
                    >
                      <div>
                        <div class="font-bold text-text-main flex items-center gap-1.5">
                          <span class="text-accent">📍</span>
                          <span>{{ stop.title }}</span>
                        </div>
                        <p v-if="stop.activity_description" class="text-text-muted mt-1 text-[11px]">
                          {{ stop.activity_description }}
                        </p>
                      </div>
                      <span v-if="stop.estimated_minutes" class="text-text-muted whitespace-nowrap text-[11px] font-medium">
                        ⏱️ ~{{ stop.estimated_minutes }} mnt
                      </span>
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
                <span>Fasilitas Termasuk</span>
              </h3>
              <p class="text-xs text-text-muted whitespace-pre-line leading-relaxed">
                {{ product.tour.inclusions || 'Transportasi, driver, BBM, tiket masuk wisata.' }}
              </p>
            </div>

            <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
              <h3 class="text-sm font-bold text-rose-700 mb-3 flex items-center gap-1.5">
                <span>❌</span>
                <span>Tidak Termasuk</span>
              </h3>
              <p class="text-xs text-text-muted whitespace-pre-line leading-relaxed">
                {{ product.tour.exclusions || 'Tiket pesawat menuju Bandara Kualanamu/Silangit, pengeluaran pribadi.' }}
              </p>
            </div>
          </div>

          <!-- Cancellation Policy Snapshot Info -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h3 class="text-sm font-bold text-text-main mb-2">Kebijakan Pembatalan & Refund Transparan</h3>
            <div class="grid grid-cols-3 gap-2 text-center text-xs mt-3">
              <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                <span class="font-bold text-emerald-800 block">> 7 Hari</span>
                <span class="text-[11px] text-emerald-600">Refund 100% dari DP</span>
              </div>
              <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                <span class="font-bold text-amber-800 block">3 - 7 Hari</span>
                <span class="text-[11px] text-amber-600">Refund 50% dari DP</span>
              </div>
              <div class="p-3 rounded-xl bg-rose-50 border border-rose-200">
                <span class="font-bold text-rose-800 block">&lt; 3 Hari</span>
                <span class="text-[11px] text-rose-600">Tidak Ada Refund</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky Booking Card (1 Col) -->
        <div>
          <div class="sticky top-20 bg-surface p-6 rounded-2xl border border-border shadow-xl space-y-5">
            <div>
              <span class="text-xs text-text-muted uppercase font-bold block">Tarif Mulai</span>
              <div class="text-2xl font-black text-primary">
                {{ formatRupiah(product.base_price) }}
                <span class="text-xs font-normal text-text-muted">/ orang</span>
              </div>
            </div>

            <!-- Participant Counter -->
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase mb-1.5">Jumlah Peserta</label>
              <div class="flex items-center gap-3">
                <button
                  @click="decrementParticipants"
                  class="w-10 h-10 rounded-xl border border-border hover:bg-surface-muted font-bold text-lg flex items-center justify-center"
                >
                  -
                </button>
                <span class="flex-1 text-center font-bold text-base text-text-main">{{ participants }} Orang</span>
                <button
                  @click="incrementParticipants"
                  class="w-10 h-10 rounded-xl border border-border hover:bg-surface-muted font-bold text-lg flex items-center justify-center"
                >
                  +
                </button>
              </div>
              <span class="text-[11px] text-text-muted mt-1 block">
                Kapasitas: Min. {{ product.tour.min_participants }} - Maks. {{ product.tour.max_participants }} orang
              </span>
            </div>

            <!-- Price Breakdown -->
            <div class="p-4 bg-surface-muted rounded-xl border border-border space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-text-muted">Total Estimasi Trip:</span>
                <span class="font-bold text-text-main">{{ formatRupiah(totalPrice) }}</span>
              </div>
              <div class="flex justify-between text-emerald-700 font-bold pt-2 border-t border-border">
                <span>Wajib DP ({{ product.dp_percentage }}%):</span>
                <span>{{ formatRupiah(dpAmount) }}</span>
              </div>
              <div class="flex justify-between text-text-muted text-[11px]">
                <span>Pelunasan s/d Hari-H:</span>
                <span>{{ formatRupiah(remainingAmount) }}</span>
              </div>
            </div>

            <!-- SLA & Lead time notice -->
            <div class="text-[11px] text-text-muted space-y-1">
              <div class="flex items-center gap-1.5">
                <span>⏱️</span>
                <span>Batas booking minimal: <strong class="text-text-main">H-1 (24 Jam)</strong></span>
              </div>
              <div class="flex items-center gap-1.5">
                <span>⚡</span>
                <span>Vendor SLA respon: <strong class="text-text-main">Maksimal 24 Jam</strong></span>
              </div>
            </div>

            <!-- CTA Button -->
            <Link
              :href="`/checkout?product_id=${product.id}&participants=${participants}`"
              class="w-full bg-accent hover:bg-accent-hover text-white font-bold py-3.5 px-4 rounded-xl text-center text-sm transition-colors shadow-lg block"
            >
              Ajukan Pemesanan Sekarang →
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
