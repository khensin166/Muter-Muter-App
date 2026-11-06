<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
});

const clusters = [
  {
    name: 'Parapat & Danau',
    desc: 'Pintu gerbang utama Danau Toba dengan pelabuhan penyeberangan Ajibata.',
    tag: 'Pelabuhan & Hub',
    count: '12 Wisata',
    icon: '🚢',
  },
  {
    name: 'Samosir Timur',
    desc: 'Pusat budaya Batak di Tomok, Sigale-gale, Ambarita, dan pesisir Tuktuk.',
    tag: 'Budaya & Sejarah',
    count: '18 Wisata',
    icon: '🏛️',
  },
  {
    name: 'Samosir Barat',
    desc: 'Bukit Holbung, Pemandian Air Panas Pangururan, dan panorama tebing kaldera.',
    tag: 'Alam & Viewpoint',
    count: '15 Wisata',
    icon: '⛰️',
  },
  {
    name: 'Merek & Tongging',
    desc: 'Air Terjun Sipiso-piso 120m, kebun teh Sidamanik, dan Taman Simalem.',
    tag: 'Highland & Curug',
    count: '9 Wisata',
    icon: '🌲',
  },
];

const sampleTours = [
  {
    id: 1,
    title: '3D2N Eksplor Eksotik Danau Toba & Budaya Samosir',
    cluster: 'Parapat - Tomok - Pangururan',
    duration: '3 Hari 2 Malam',
    type: 'Private Trip',
    price: 'Rp 1.450.000',
    unit: '/ orang',
    dpPercent: '30%',
    dpAmount: 'Rp 435.000',
    includesFerry: true,
    highlights: ['Ferry Penyeberangan Termasuk', 'Bukit Holbung', 'Makam Raja Sidabutar', 'Batu Kursi Siallagan'],
  },
  {
    id: 2,
    title: 'One Day Trip Samosir Heritage & Viewpoint Tele',
    cluster: 'Samosir East & West',
    duration: '1 Hari Penuh (10 Jam)',
    type: 'Open Trip',
    price: 'Rp 380.000',
    unit: '/ orang',
    dpPercent: '30%',
    dpAmount: 'Rp 114.000',
    includesFerry: true,
    highlights: ['Ferry PP Ajibata-Tomok', 'Tari Sigale-Gale', 'Menara Pandang Tele', 'Local Guide'],
  },
  {
    id: 3,
    title: '2D1N Highlands Adventure Sipiso-piso & Tongging',
    cluster: 'Merek / Tongging',
    duration: '2 Hari 1 Malam',
    type: 'Private Trip',
    price: 'Rp 950.000',
    unit: '/ orang',
    dpPercent: '30%',
    dpAmount: 'Rp 285.000',
    includesFerry: false,
    highlights: ['Curug Sipiso-piso', 'Sunrise Caldera View', 'Glamping Highland Lake Toba', 'Transport Medan PP'],
  },
];

const sampleVehicles = [
  {
    id: 1,
    name: 'Toyota All New Avanza',
    type: 'Mobil MPV (7 Kursi)',
    capacity: '6 Penumpang',
    luggage: '3 Koper',
    transmission: 'Manual / Matic',
    withDriver: true,
    price: 'Rp 650.000',
    unit: '/ hari (Termasuk Driver & BBM)',
    leadTime: 'H-12 Jam',
  },
  {
    id: 2,
    name: 'Toyota Innova Reborn Diesel',
    type: 'Medium MPV Premium',
    capacity: '7 Penumpang',
    luggage: '4 Koper',
    transmission: 'Automatic',
    withDriver: true,
    price: 'Rp 850.000',
    unit: '/ hari (Kenyamanan Ekstra Jalur Kaldera)',
    leadTime: 'H-12 Jam',
  },
  {
    id: 3,
    name: 'Toyota HiAce Commuter 14-Seat',
    type: 'Minibus Rombongan / Keluarga',
    capacity: '14 Penumpang',
    luggage: '8 Koper',
    transmission: 'Manual',
    withDriver: true,
    price: 'Rp 1.350.000',
    unit: '/ hari (Ideal untuk Tour Rombongan)',
    leadTime: 'H-12 Jam',
  },
];
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
          Temukan pesona Danau Toba, pesan paket tour kurasi, sewa kendaraan lokal berizin, dan konsultasikan perjalanan impian Anda dengan transparan.
        </p>

        <!-- Search / Filter Card -->
        <div class="mt-10 max-w-4xl mx-auto bg-surface rounded-2xl p-4 sm:p-6 shadow-2xl border border-border text-left text-text-main">
          <div class="flex items-center gap-2 border-b border-border pb-3 mb-4 text-xs sm:text-sm font-bold">
            <button class="px-4 py-2 rounded-xl bg-primary text-white">🎒 Paket Wisata</button>
            <button class="px-4 py-2 rounded-xl hover:bg-surface-muted text-text-muted transition">🚐 Rental Mobil</button>
            <button class="px-4 py-2 rounded-xl hover:bg-surface-muted text-text-muted transition">🗺️ Custom Trip</button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Destinasi / Klaster</label>
              <select class="w-full text-sm font-medium border-border rounded-xl focus:border-primary focus:ring-primary bg-background">
                <option>Semua Kawasan Danau Toba</option>
                <option>Parapat (Gerbang Utama)</option>
                <option>Samosir Timur (Tomok & Tuktuk)</option>
                <option>Samosir Barat (Pangururan & Holbung)</option>
                <option>Merek & Tongging (Sipiso-piso)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Tanggal Keberangkatan</label>
              <input type="date" class="w-full text-sm font-medium border-border rounded-xl focus:border-primary focus:ring-primary bg-background" />
            </div>
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">Jumlah Tamu</label>
              <select class="w-full text-sm font-medium border-border rounded-xl focus:border-primary focus:ring-primary bg-background">
                <option>2 - 4 Orang (Keluarga Kecil)</option>
                <option>5 - 7 Orang (Medium Group)</option>
                <option>8 - 14 Orang (Minibus HiAce)</option>
                <option>> 15 Orang (Rombongan Bus)</option>
              </select>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-border flex flex-col sm:flex-row justify-between items-center gap-3">
            <span class="text-xs text-text-muted flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-secondary"></span>
              Booking H-1 untuk Tour, H-12 Jam untuk Rental Kendaraan
            </span>
            <button class="w-full sm:w-auto bg-accent hover:bg-accent-hover text-white text-sm font-bold py-2.5 px-6 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
              <span>🔍</span>
              <span>Cari Perjalanan</span>
            </button>
          </div>
        </div>

        <!-- Quick navigation to Portals (for testing preview) -->
        <div class="mt-8 flex flex-wrap justify-center items-center gap-3 text-xs">
          <span class="text-slate-300">Cek Demo Portal:</span>
          <Link href="/partner" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 font-semibold transition">
            🏢 Portal Mitra Vendor (SLA 24h & Armada) →
          </Link>
          <Link href="/admin" class="px-3 py-1.5 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 text-purple-200 border border-purple-400/30 font-semibold transition">
            🛡️ Admin Operations (Verifikasi DP & KYC) →
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
        <div
          v-for="cluster in clusters"
          :key="cluster.name"
          class="bg-surface rounded-2xl p-6 border border-border hover:border-primary/40 hover:shadow-card-hover transition-all group flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-3xl">{{ cluster.icon }}</span>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-secondary-light text-secondary">
                {{ cluster.tag }}
              </span>
            </div>
            <h3 class="text-lg font-bold text-text-main group-hover:text-primary transition-colors">
              {{ cluster.name }}
            </h3>
            <p class="text-xs text-text-muted mt-2 leading-relaxed">
              {{ cluster.desc }}
            </p>
          </div>
          <div class="mt-6 pt-3 border-t border-border flex items-center justify-between text-xs font-bold text-primary">
            <span>{{ cluster.count }}</span>
            <span class="group-hover:translate-x-1 transition-transform">Eksplor →</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Tour Packages Section -->
    <section class="py-16 bg-surface-muted border-y border-border">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
          <div>
            <span class="text-xs font-bold text-secondary uppercase tracking-widest">Paket Pilihan</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">Paket Wisata Danau Toba & Samosir</h2>
          </div>
          <a href="#" class="text-sm font-bold text-primary hover:text-primary-dark transition mt-2 md:mt-0">
            Lihat Semua Paket (15+) →
          </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div
            v-for="tour in sampleTours"
            :key="tour.id"
            class="bg-surface rounded-2xl border border-border overflow-hidden hover:shadow-card-hover transition flex flex-col justify-between"
          >
            <!-- Card Body -->
            <div class="p-6">
              <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full bg-primary/10 text-primary">
                  {{ tour.type }}
                </span>
                <span class="text-xs text-text-muted font-medium">⏱️ {{ tour.duration }}</span>
              </div>

              <h3 class="text-base font-bold text-text-main hover:text-primary transition-colors line-clamp-2">
                {{ tour.title }}
              </h3>
              <p class="text-xs text-text-muted mt-1.5 flex items-center gap-1">
                <span>📍</span>
                <span>{{ tour.cluster }}</span>
              </p>

              <!-- Ferry Inclusion Notice -->
              <div v-if="tour.includesFerry" class="mt-3 py-1.5 px-3 rounded-lg bg-emerald-50 border border-emerald-200/60 flex items-center gap-2 text-xs font-semibold text-emerald-800">
                <span>🚢</span>
                <span>Termasuk Tiket Ferry Penyeberangan Danau</span>
              </div>

              <!-- Highlights list -->
              <div class="mt-4 space-y-1.5">
                <div v-for="h in tour.highlights" :key="h" class="text-xs text-text-muted flex items-center gap-2">
                  <span class="text-secondary font-bold">✓</span>
                  <span>{{ h }}</span>
                </div>
              </div>
            </div>

            <!-- Card Footer Pricing -->
            <div class="p-6 pt-4 border-t border-border bg-background/50 flex flex-col gap-3">
              <div class="flex items-baseline justify-between">
                <div>
                  <span class="text-xs text-text-muted block">Mulai dari</span>
                  <span class="text-lg font-black text-text-main">{{ tour.price }}</span>
                  <span class="text-xs text-text-muted">{{ tour.unit }}</span>
                </div>
                <div class="text-right">
                  <span class="text-[11px] text-text-muted block">DP Awal ({{ tour.dpPercent }})</span>
                  <span class="text-xs font-bold text-accent">{{ tour.dpAmount }}</span>
                </div>
              </div>

              <button class="w-full bg-accent hover:bg-accent-hover text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition">
                Pesan Sekarang (DP {{ tour.dpPercent }})
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Vehicle Rentals Section -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-widest">Transportasi</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-text-main mt-1">Sewa Kendaraan & Minibus Terverifikasi</h2>
        </div>
        <p class="text-sm text-text-muted mt-2 md:mt-0 max-w-md">
          Unit terawat dengan pengemudi berpengalaman menguasai jalur perbukitan dan tikungan kaldera Danau Toba.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="vehicle in sampleVehicles"
          :key="vehicle.id"
          class="bg-surface rounded-2xl border border-border p-6 hover:shadow-card-hover transition flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-primary bg-primary/10 px-2.5 py-0.5 rounded-full">
                {{ vehicle.type }}
              </span>
              <span class="text-[11px] text-text-muted font-semibold bg-amber-50 text-amber-800 border border-amber-200 px-2 py-0.5 rounded">
                Lead Time {{ vehicle.leadTime }}
              </span>
            </div>
            <h3 class="text-lg font-bold text-text-main mt-1">{{ vehicle.name }}</h3>

            <div class="grid grid-cols-2 gap-2 mt-4 text-xs text-text-muted bg-background p-3 rounded-xl border border-border">
              <div>👥 {{ vehicle.capacity }}</div>
              <div>🧳 {{ vehicle.luggage }}</div>
              <div>⚙️ {{ vehicle.transmission }}</div>
              <div>👨‍✈️ {{ vehicle.withDriver ? 'Dengan Driver' : 'Lepas Kunci' }}</div>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
            <div>
              <span class="text-base font-extrabold text-text-main block">{{ vehicle.price }}</span>
              <span class="text-[11px] text-text-muted">{{ vehicle.unit }}</span>
            </div>
            <button class="bg-primary hover:bg-primary-dark text-white text-xs font-bold py-2 px-4 rounded-xl transition">
              Cek Armada
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Trust & Value Proposition -->
    <section class="py-16 bg-primary text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="space-y-2 p-6 bg-white/5 rounded-2xl border border-white/10">
          <div class="text-3xl mb-2">⏱️</div>
          <h4 class="text-lg font-bold">24-Hour Partner SLA</h4>
          <p class="text-xs text-slate-200 leading-relaxed">
            Setiap permintaan ketersediaan diverifikasi maksimal 24 jam oleh mitra terdaftar dengan garansi intervensi admin.
          </p>
        </div>
        <div class="space-y-2 p-6 bg-white/5 rounded-2xl border border-white/10">
          <div class="text-3xl mb-2">🔒</div>
          <h4 class="text-lg font-bold">Transparansi DP & Refund</h4>
          <p class="text-xs text-slate-200 leading-relaxed">
            Cukup bayar DP 30% untuk kunci jadwal dengan kebijakan refund transparan hingga 100% jika batal sebelum H-7.
          </p>
        </div>
        <div class="space-y-2 p-6 bg-white/5 rounded-2xl border border-white/10">
          <div class="text-3xl mb-2">📲</div>
          <h4 class="text-lg font-bold">WhatsApp Direct Integration</h4>
          <p class="text-xs text-slate-200 leading-relaxed">
            Update status pesanan, rincian tagihan rekening, dan voucher instan dikirim otomatis ke WhatsApp Anda.
          </p>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
