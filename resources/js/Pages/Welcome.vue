<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

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

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};

const clusterIcons = {
  PARAPAT: '🚢',
  SAMOSIR_EAST: '🏛️',
  SAMOSIR_WEST: '⛰️',
  MEREK_TONGGING: '🌲',
};
</script>

<template>
  <AppLayout>
    <Head title="Lake Toba Travel Marketplace — Muter-Muter Danau Toba" />

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-b from-primary-dark via-primary to-primary-light text-white py-16 md:py-24 overflow-hidden">
      <!-- Caldera contour background decorative SVG -->
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full object-cover" viewBox="0 0 100 100" preserveAspectRatio="none">
          <path d="M0,0 C30,40 70,20 100,50 L100,100 L0,100 Z" fill="currentColor" />
        </svg>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-md px-3.5 py-1.5 rounded-full text-xs font-semibold mb-6 border border-white/20">
          <span>✨</span>
          <span>Marketplace Perjalanan Terintegrasi Danau Toba</span>
        </div>

        <h1 class="text-3xl sm:text-5xl md:text-6xl font-black tracking-tight max-w-4xl mx-auto leading-tight">
          One Platform to Discover, Plan, and Experience
          <span class="text-accent underline decoration-accent/40 underline-offset-8">North Sumatra</span>
        </h1>

        <p class="mt-6 text-base sm:text-lg md:text-xl text-slate-200 max-w-2xl mx-auto font-light leading-relaxed">
          Temukan pesona Danau Toba, pesan paket tour kurasi lokal, sewa armada kendaraan berizin, dan nikmati liburan aman dengan skema Down Payment (DP) transparan.
        </p>

        <!-- Search / Filter Card -->
        <div class="mt-10 max-w-4xl mx-auto bg-surface rounded-2xl p-4 sm:p-6 shadow-2xl border border-border text-left text-text-main">
          <div class="flex items-center gap-2 border-b border-border pb-3 mb-4 text-xs sm:text-sm font-bold">
            <button
              @click="activeSearchTab = 'tour'"
              :class="activeSearchTab === 'tour' ? 'bg-primary text-white' : 'hover:bg-surface-muted text-text-muted'"
              class="px-4 py-2 rounded-xl transition"
            >
              🎒 Paket Wisata
            </button>
            <button
              @click="activeSearchTab = 'vehicle'"
              :class="activeSearchTab === 'vehicle' ? 'bg-primary text-white' : 'hover:bg-surface-muted text-text-muted'"
              class="px-4 py-2 rounded-xl transition"
            >
              🚐 Rental Mobil
            </button>
            <Link
              href="/destinations"
              class="px-4 py-2 rounded-xl hover:bg-surface-muted text-text-muted transition inline-flex items-center gap-1.5"
            >
              🗺️ Destinasi Wisata
            </Link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Pilih Kawasan / Klaster</label>
              <select v-model="selectedCluster" class="w-full text-sm font-medium border-border rounded-xl focus:border-primary focus:ring-primary bg-background">
                <option value="">Semua Kawasan Danau Toba</option>
                <option v-for="c in clusters" :key="c.value" :value="c.value">
                  {{ c.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Lead Time Pemesanan</label>
              <div class="w-full py-2.5 px-3 text-xs font-semibold bg-surface-muted rounded-xl text-text-muted border border-border">
                ⏱️ Tour H-1 (24 Jam) | Sewa H-12 Jam
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Skema Pembayaran</label>
              <div class="w-full py-2.5 px-3 text-xs font-semibold bg-surface-muted rounded-xl text-primary border border-border">
                🛡️ DP 30% Kunci Unit + Hold 24 Jam
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-border flex flex-col sm:flex-row justify-between items-center gap-3">
            <span class="text-xs text-text-muted flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-secondary"></span>
              Satu pintu verifikasi mitra lokal & jaminan tiket ferry terjadwal
            </span>
            <Link
              :href="activeSearchTab === 'tour' ? `/tours${selectedCluster ? '?cluster=' + selectedCluster : ''}` : `/vehicles`"
              class="w-full sm:w-auto bg-accent hover:bg-accent-hover text-white text-sm font-bold py-2.5 px-6 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2"
            >
              <span>🔍</span>
              <span>Lihat Katalog Lengkap</span>
            </Link>
          </div>
        </div>

        <!-- Quick navigation to Portals -->
        <div class="mt-8 flex flex-wrap justify-center items-center gap-3 text-xs">
          <span class="text-slate-300">Akses Cepat Pengguna:</span>
          <Link href="/destinations" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium transition">
            📍 10 Destinasi Danau Toba
          </Link>
          <Link href="/partner" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 font-semibold transition">
            🏢 Portal Mitra Vendor →
          </Link>
          <Link href="/admin" class="px-3 py-1.5 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 text-purple-200 border border-purple-400/30 font-semibold transition">
            🛡️ Admin Operations →
          </Link>
        </div>
      </div>
    </section>

    <!-- Geographic Clusters Section -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-widest">Kawasan Wisata</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">4 Klaster Utama Danau Toba (MVP)</h2>
        </div>
        <p class="text-sm text-text-muted mt-2 md:mt-0 max-w-md">
          Eksplorasi wilayah keindahan danau terbesar di Asia Tenggara dari titik pelabuhan hingga dataran tinggi kaldera.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <Link
          v-for="cluster in clusters"
          :key="cluster.value"
          :href="`/destinations?cluster=${cluster.value}`"
          class="bg-surface rounded-2xl p-6 border border-border hover:border-primary/40 hover:shadow-card-hover transition-all group flex flex-col justify-between cursor-pointer"
        >
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-3xl">{{ clusterIcons[cluster.value] || '📍' }}</span>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-secondary-light text-secondary">
                Klaster Aktif
              </span>
            </div>
            <h3 class="text-lg font-bold text-text-main group-hover:text-primary transition-colors">
              {{ cluster.label }}
            </h3>
            <p class="text-xs text-text-muted mt-2 leading-relaxed">
              Jelajahi objek wisata, panorama bukit, dan pesona budaya Batak di kawasan {{ cluster.label }}.
            </p>
          </div>
          <div class="mt-6 pt-3 border-t border-border flex items-center justify-between text-xs font-bold text-primary">
            <span>Lihat Objek Wisata</span>
            <span class="group-hover:translate-x-1 transition-transform">Eksplor →</span>
          </div>
        </Link>
      </div>
    </section>

    <!-- Featured Destinations Section -->
    <section class="py-12 bg-surface-muted border-y border-border">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
          <div>
            <span class="text-xs font-bold text-secondary uppercase tracking-widest">Eksplorasi Objek</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">Destinasi Populer Danau Toba</h2>
          </div>
          <Link href="/destinations" class="text-sm font-bold text-primary hover:text-primary-dark mt-2 md:mt-0 flex items-center gap-1">
            Lihat Semua 10 Destinasi →
          </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link
            v-for="dest in featuredDestinations"
            :key="dest.id"
            :href="`/destinations/${dest.slug}`"
            class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover hover:border-primary/30 transition-all flex flex-col group"
          >
            <div class="relative h-48 overflow-hidden bg-slate-100">
              <img :src="dest.image_url" :alt="dest.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-primary shadow-sm">
                {{ dest.cluster_label }}
              </div>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <span class="text-[11px] font-semibold text-secondary uppercase">{{ dest.category || 'Wisata Alam' }}</span>
                <h3 class="text-base font-bold text-text-main mt-1 group-hover:text-primary transition-colors">
                  {{ dest.name }}
                </h3>
              </div>
              <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs">
                <span class="text-text-muted">Tiket Masuk:</span>
                <span class="font-bold text-primary">
                  {{ dest.ticket_price_domestic > 0 ? formatRupiah(dest.ticket_price_domestic) : 'Gratis' }}
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
          <span class="text-xs font-bold text-accent uppercase tracking-widest">Katalog Paket Tour</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">Paket Wisata Siap Berangkat</h2>
          <p class="text-sm text-text-muted mt-1">Termasuk tiket ferry penyeberangan Danau Toba, transportasi privat, dan guide lokal.</p>
        </div>
        <Link href="/tours" class="text-sm font-bold text-primary hover:text-primary-dark mt-2 md:mt-0 flex items-center gap-1">
          Lihat Semua Paket Tour →
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="tour in featuredTours"
          :key="tour.id"
          class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover hover:border-primary/30 transition-all flex flex-col"
        >
          <div class="relative h-48 overflow-hidden bg-slate-200">
            <img :src="tour.image_url" :alt="tour.name" class="w-full h-full object-cover" />
            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
              <span class="bg-primary/90 text-white text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur-sm">
                {{ tour.duration_days }}H{{ tour.duration_nights }}M
              </span>
              <span v-if="tour.includes_ferry" class="bg-secondary/90 text-white text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur-sm flex items-center gap-1">
                🚢 Ferry Termasuk
              </span>
            </div>
          </div>

          <div class="p-6 flex-1 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between text-xs text-text-muted mb-2">
                <span>{{ tour.tour_type }}</span>
                <span class="text-secondary font-semibold">Min. H-{{ tour.min_lead_time_hours / 24 }} Hari</span>
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
                  <span class="text-[10px] uppercase font-bold text-text-muted">Mulai Dari</span>
                  <div class="text-xl font-black text-primary">
                    {{ formatRupiah(tour.base_price) }}
                    <span class="text-xs font-normal text-text-muted">/ orang</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-[10px] uppercase font-bold text-emerald-600 block">DP Cukup {{ tour.dp_percentage }}%</span>
                  <span class="text-xs font-bold text-slate-700">{{ formatRupiah(tour.base_price * (tour.dp_percentage / 100)) }}</span>
                </div>
              </div>

              <Link
                :href="`/tours/${tour.slug}`"
                class="mt-4 w-full bg-primary hover:bg-primary-dark text-white font-bold py-2.5 px-4 rounded-xl text-center text-sm transition-colors block"
              >
                Detail & Jadwal Perjalanan
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
            <span class="text-xs font-bold text-secondary uppercase tracking-widest">Armada Transportasi</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">Sewa Kendaraan Lokal Berizin</h2>
            <p class="text-sm text-text-muted mt-1">Armada terawat, pengemudi lokal berpengalaman rute tanjakan kaldera, siap jemput di Silangit / Medan / Parapat.</p>
          </div>
          <Link href="/vehicles" class="text-sm font-bold text-primary hover:text-primary-dark mt-2 md:mt-0 flex items-center gap-1">
            Lihat Semua Unit Rental →
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="veh in featuredVehicles"
            :key="veh.id"
            class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover transition-all flex flex-col"
          >
            <div class="relative h-44 overflow-hidden bg-slate-100">
              <img :src="veh.image_url" :alt="veh.name" class="w-full h-full object-cover" />
              <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-slate-800 shadow-sm">
                Min. Booking H-{{ veh.min_lead_time_hours }} Jam
              </div>
            </div>

            <div class="p-6 flex-1 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between text-xs text-text-muted mb-1">
                  <span>{{ veh.brand }} {{ veh.year }}</span>
                  <span class="font-semibold text-emerald-600">{{ veh.with_driver ? 'Termasuk Driver' : 'Lepas Kunci' }}</span>
                </div>
                <h3 class="text-lg font-bold text-text-main">
                  <Link :href="`/vehicles/${veh.slug}`">{{ veh.name }}</Link>
                </h3>

                <div class="mt-3 flex items-center gap-4 text-xs text-text-muted font-medium bg-surface-muted p-2.5 rounded-xl border border-border">
                  <div class="flex items-center gap-1">
                    <span>👥</span>
                    <span>{{ veh.passenger_capacity }} Orang</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span>🧳</span>
                    <span>{{ veh.luggage_capacity }} Koper</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span>⚙️</span>
                    <span>{{ veh.transmission }}</span>
                  </div>
                </div>
              </div>

              <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                <div>
                  <span class="text-[10px] uppercase font-bold text-text-muted">Tarif Sewa</span>
                  <div class="text-xl font-black text-primary">
                    {{ formatRupiah(veh.base_price) }}
                    <span class="text-xs font-normal text-text-muted">/ hari</span>
                  </div>
                </div>
                <Link
                  :href="`/vehicles/${veh.slug}`"
                  class="bg-accent hover:bg-accent-hover text-white font-bold py-2 px-4 rounded-xl text-xs transition-colors shadow-sm"
                >
                  Sewa Unit
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us / Guarantees -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="text-xs font-bold text-accent uppercase tracking-widest">Jaminan Keamanan Marketplace</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">Mengapa Memesan di Muter-Muter Danau Toba?</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="p-5 rounded-2xl border border-border bg-surface text-center">
          <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-2xl mx-auto mb-4">
            🛡️
          </div>
          <h4 class="font-bold text-text-main text-sm">Mitra Lokal Terverifikasi</h4>
          <p class="text-xs text-text-muted mt-2">Seluruh operator tour dan penyedia transportasi melewati verifikasi KYC legalitas & fisik.</p>
        </div>

        <div class="p-5 rounded-2xl border border-border bg-surface text-center">
          <div class="w-12 h-12 rounded-2xl bg-secondary/10 text-secondary flex items-center justify-center text-2xl mx-auto mb-4">
            💳
          </div>
          <h4 class="font-bold text-text-main text-sm">Skema DP Fleksibel</h4>
          <p class="text-xs text-text-muted mt-2">Cukup bayar DP 30% untuk mengunci jadwal ketersediaan. Pelunasan aman sebelum keberangkatan.</p>
        </div>

        <div class="p-5 rounded-2xl border border-border bg-surface text-center">
          <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-2xl mx-auto mb-4">
            ⚡
          </div>
          <h4 class="font-bold text-text-main text-sm">SLA Konfirmasi 24 Jam</h4>
          <p class="text-xs text-text-muted mt-2">Vendor wajib merespons dalam 24 jam. Jika terlambat, sistem mengalihkan ke intervensi manual Admin.</p>
        </div>

        <div class="p-5 rounded-2xl border border-border bg-surface text-center">
          <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-4">
            🚢
          </div>
          <h4 class="font-bold text-text-main text-sm">Integrasi Jadwal Ferry</h4>
          <p class="text-xs text-text-muted mt-2">Paket perjalanan sudah memperhitungkan penyeberangan kapal ferry Danau Toba tanpa biaya siluman.</p>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
