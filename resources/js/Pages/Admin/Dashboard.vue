<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  metrics: {
    type: Object,
    default: () => ({}),
  },
  urgentPayments: {
    type: Array,
    default: () => [],
  },
  urgentSla: {
    type: Array,
    default: () => [],
  },
  recentBookings: {
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
  availablePartners: {
    type: Array,
    default: () => [],
  },
});


// Dispatch Modal State
const selectedBookingForDispatch = ref(null);
const dispatchForm = useForm({
  partner_id: '',
  assigned_driver_name: '',
  assigned_driver_phone: '',
  assigned_vehicle_plate: '',
});

const openDispatchModal = (booking) => {
  selectedBookingForDispatch.value = booking;
  dispatchForm.partner_id = booking.partner_id || (props.availablePartners[0]?.id ?? '');
  dispatchForm.assigned_driver_name = booking.assigned_driver_name || '';
  dispatchForm.assigned_driver_phone = '';
  dispatchForm.assigned_vehicle_plate = booking.assigned_vehicle_plate || '';
};

const closeDispatchModal = () => {
  selectedBookingForDispatch.value = null;
  dispatchForm.reset();
};

const submitDispatch = () => {
  if (!selectedBookingForDispatch.value) return;
  dispatchForm.post(route('admin.bookings.dispatch', selectedBookingForDispatch.value.id), {
    preserveScroll: true,
    onSuccess: () => closeDispatchModal(),
  });
};

const statusPillClass = (status) => {
  switch (status) {
    case 'CONFIRMED':
    case 'COMPLETED':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200/70';
    case 'AWAITING_PAYMENT':
    case 'PAYMENT_REVIEW':
    case 'PENDING_APPROVAL':
      return 'bg-amber-50 text-amber-800 border-amber-200/70';
    case 'MANUAL_REVIEW':
      return 'bg-rose-50 text-rose-700 border-rose-200/70';
    default:
      return 'bg-slate-100 text-slate-600 border-slate-200';
  }
};

const statusDotClass = (status) => {
  switch (status) {
    case 'CONFIRMED':
    case 'COMPLETED':
      return 'bg-emerald-500';
    case 'AWAITING_PAYMENT':
    case 'PAYMENT_REVIEW':
    case 'PENDING_APPROVAL':
      return 'bg-amber-500 animate-pulse';
    case 'MANUAL_REVIEW':
      return 'bg-rose-500 animate-pulse';
    default:
      return 'bg-slate-400';
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="Pusat Kendali Operasional — Admin Console" />

    <!-- Enterprise Page Header -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 mb-6 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 text-[11px] font-bold text-primary uppercase tracking-wider mb-1">
          <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
          <span>Pusat Kendali Operasional & Katalog Master</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
          Ikhtisar Ekosistem Muter-Muter Sumatera Utara
        </h1>
        <p class="text-xs text-slate-500 mt-1 max-w-2xl">
          Kelola destinasi wisata master, susun paket tur resmi terkurasi, pantau verifikasi pembayaran DP, serta dispatch pesanan ke armada mitra terbaik.
        </p>
      </div>

      <!-- Quick Action Bar -->
      <div class="flex flex-wrap items-center gap-2.5">
        <Link
          href="/admin/destinations"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-800 text-xs font-bold transition"
        >
          <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
          </svg>
          <span>Kelola Destinasi ({{ metrics.total_destinations || 0 }})</span>
        </Link>

        <Link
          href="/admin/tours/create"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>Buat Paket Wisata Baru</span>
        </Link>
      </div>
    </div>

    <!-- Row 1: Vital Operations Metrics (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- 1. Pending DP Verification -->
      <Link
        :href="route('admin.payments.index')"
        class="p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-primary/50 shadow-2xs transition-all group flex flex-col justify-between"
      >
        <div class="flex items-start justify-between">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Verifikasi Bukti DP</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-1 tabular-nums group-hover:text-primary transition-colors">
              {{ metrics.pending_payments || 0 }}
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-700 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
          <span class="text-amber-700 font-semibold">Menunggu validasi mutasi</span>
          <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">→</span>
        </div>
      </Link>

      <!-- 2. SLA Breached -->
      <Link
        :href="route('admin.sla.index')"
        class="p-5 bg-white rounded-2xl border shadow-2xs transition-all group flex flex-col justify-between"
        :class="metrics.sla_breached > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80 hover:border-rose-300'"
      >
        <div class="flex items-start justify-between">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Intervensi SLA (24h)</span>
            <div
              class="text-3xl font-extrabold mt-1 tabular-nums"
              :class="metrics.sla_breached > 0 ? 'text-rose-600' : 'text-slate-900'"
            >
              {{ metrics.sla_breached || 0 }}
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200/60 text-rose-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
          <span class="text-rose-600 font-semibold">Mitra >24 jam belum respon</span>
          <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">→</span>
        </div>
      </Link>

      <!-- 3. Partner KYC & Active Fleet -->
      <Link
        :href="route('admin.partners.index')"
        class="p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-primary/50 shadow-2xs transition-all group flex flex-col justify-between"
      >
        <div class="flex items-start justify-between">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mitra & Armada Aktif</span>
            <div class="flex items-baseline gap-2 mt-1">
              <span class="text-3xl font-extrabold text-slate-900 tabular-nums">{{ metrics.approved_partners || 0 }}</span>
              <span class="text-xs font-semibold text-slate-500">Mitra ({{ metrics.active_vehicles || 0 }} Unit)</span>
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200/60 text-indigo-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
            </svg>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
          <span class="text-slate-600 font-medium">{{ metrics.pending_partners || 0 }} pengajuan KYC baru</span>
          <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">→</span>
        </div>
      </Link>

      <!-- 4. Platform Revenue & Settlements -->
      <Link
        :href="route('admin.settlements.index')"
        class="p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-emerald-400 shadow-2xs transition-all group flex flex-col justify-between"
      >
        <div class="flex items-start justify-between">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Komisi Platform (10%)</span>
            <div class="text-2xl font-extrabold text-emerald-700 mt-1 tabular-nums">
              {{ formatRupiah(metrics.total_commission_collected) }}
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
          <span class="text-slate-600 font-medium">Siap cair: {{ formatRupiah(metrics.eligible_payout_total) }}</span>
          <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">→</span>
        </div>
      </Link>
    </div>

    <!-- Row 2: Master Destinations & Curated Tour Packages Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Card A: Master Destinations Overview -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-2xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
              </div>
              <div>
                <h2 class="text-sm font-bold text-slate-900">Katalog Destinasi Wisata Master</h2>
                <p class="text-[11px] text-slate-500">Terhubung otomatis dengan galeri foto rute paket wisata</p>
              </div>
            </div>
            <Link
              href="/admin/destinations"
              class="text-xs font-bold text-primary hover:text-primary-dark inline-flex items-center gap-1"
            >
              <span>Kelola ({{ metrics.total_destinations || 0 }})</span>
              <span>→</span>
            </Link>
          </div>

          <div class="space-y-2.5">
            <div
              v-for="dest in featuredDestinations"
              :key="dest.id"
              class="p-3 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 flex items-center justify-between gap-3 transition"
            >
              <div class="flex items-center gap-3 min-w-0">
                <img
                  :src="dest.image_url"
                  :alt="dest.name"
                  class="w-11 h-11 rounded-lg object-cover shrink-0 border border-slate-200"
                />
                <div class="min-w-0">
                  <div class="text-xs font-bold text-slate-900 truncate">{{ dest.name }}</div>
                  <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                    <span class="font-medium text-primary">{{ dest.cluster_label }}</span>
                    <span>•</span>
                    <span>{{ dest.media_count }} Foto</span>
                  </div>
                </div>
              </div>
              <div class="text-right shrink-0">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white border border-slate-200 text-slate-700">
                  {{ dest.stops_count }} Rute Paket
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
          <span class="text-slate-500 text-[11px]">Foto destinasi otomatis tampil di paket tour yang melewatinya.</span>
          <Link
            href="/admin/destinations"
            class="font-bold text-primary hover:underline text-xs"
          >
            + Tambah Destinasi Baru
          </Link>
        </div>
      </div>

      <!-- Card B: Curated Tour Packages Overview -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-2xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-700 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                </svg>
              </div>
              <div>
                <h2 class="text-sm font-bold text-slate-900">Paket Wisata Terkurasi & Rute Aktif</h2>
                <p class="text-[11px] text-slate-500">{{ metrics.published_tours || 0 }} paket tayang di katalog publik</p>
              </div>
            </div>
            <Link
              href="/admin/tours/create"
              class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition"
            >
              + Buat Paket
            </Link>
          </div>

          <div class="space-y-2.5">
            <div
              v-for="tour in featuredTours"
              :key="tour.id"
              class="p-3 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 flex items-center justify-between gap-3 transition"
            >
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <Link :href="`/tours/${tour.slug}`" class="text-xs font-bold text-slate-900 hover:text-primary truncate">
                    {{ tour.name }}
                  </Link>
                  <span
                    v-if="tour.ownership_type === 'INTERNAL'"
                    class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-primary/10 text-primary shrink-0"
                  >
                    RESMI PLATFORM
                  </span>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                  <span class="text-[11px] text-slate-500 font-medium">{{ tour.duration_days }} Hari</span>
                  <span class="text-slate-300">•</span>
                  <span class="text-[11px] text-slate-500">{{ tour.stops_count }} Titik Kunjungan</span>
                  <template v-if="tour.bound_destinations && tour.bound_destinations.length > 0">
                    <span class="text-slate-300">•</span>
                    <span class="text-[10px] text-emerald-700 font-semibold truncate max-w-[220px]">
                      📍 {{ tour.bound_destinations.slice(0, 2).join(', ') }}
                      <template v-if="tour.bound_destinations.length > 2">+{{ tour.bound_destinations.length - 2 }}</template>
                    </span>
                  </template>
                </div>
              </div>
              <div class="text-right shrink-0">
                <div class="text-xs font-extrabold text-slate-900 tabular-nums">{{ formatRupiah(tour.base_price) }}</div>
                <span class="text-[10px] font-semibold text-emerald-600">Published</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
          <span class="text-slate-500 text-[11px]">Mendukung template 6D5N Famtrip & 1D Samosir</span>
          <Link
            href="/admin/products"
            class="font-bold text-slate-700 hover:text-primary text-xs"
          >
            Lihat Semua Produk →
          </Link>
        </div>
      </div>
    </div>

    <!-- Row 3: Pending Payments & Recent Bookings with Partner Dispatch -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left 5 Cols: Pending Payment Proofs -->
      <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-2xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
          <div>
            <h3 class="text-sm font-bold text-slate-900">Antrean Verifikasi Bukti DP</h3>
            <p class="text-[11px] text-slate-500">Validasi mutasi transfer bank (DP 30%)</p>
          </div>
          <Link
            :href="route('admin.payments.index')"
            class="text-xs text-primary font-bold hover:underline"
          >
            Semua Antrean →
          </Link>
        </div>

        <div v-if="urgentPayments.length === 0" class="py-10 text-center">
          <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <p class="text-xs font-semibold text-slate-600">Semua bukti transfer sudah terverifikasi</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada antrean tertunda saat ini.</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="p in urgentPayments"
            :key="p.id"
            class="p-3.5 bg-slate-50/70 rounded-xl border border-slate-200/70 flex items-center justify-between text-xs"
          >
            <div>
              <div class="font-mono font-bold text-slate-900">{{ p.booking_code }}</div>
              <div class="font-semibold text-slate-700 text-[11px] mt-0.5">{{ p.customer_name }}</div>
              <div class="text-slate-400 text-[10px] mt-0.5">{{ p.bank_sender }} • {{ p.created_at }}</div>
            </div>
            <div class="text-right">
              <div class="font-extrabold text-slate-900 text-sm tabular-nums">{{ formatRupiah(p.amount) }}</div>
              <Link
                :href="route('admin.payments.show', p.id)"
                class="mt-1.5 inline-flex items-center px-2.5 py-1 bg-primary text-white rounded-lg text-[10px] font-bold hover:bg-primary-dark transition"
              >
                Periksa Bukti
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 7 Cols: Recent Bookings & Partner Dispatching -->
      <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-2xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
          <div>
            <h3 class="text-sm font-bold text-slate-900">Pesanan Terbaru & Dispatch Armada Mitra</h3>
            <p class="text-[11px] text-slate-500">Tugaskan mitra dan supir terbaik untuk pesanan paket wisata</p>
          </div>
          <span class="text-[11px] font-semibold text-slate-500">Total: {{ metrics.total_bookings || 0 }} Pesanan</span>
        </div>

        <div v-if="recentBookings.length === 0" class="py-10 text-center text-slate-400 text-xs">
          Belum ada transaksi pemesanan tercatat.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200/80 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                <th class="py-2.5 pr-3">Kode & Tamu</th>
                <th class="py-2.5 px-3">Paket / Armada Mitra</th>
                <th class="py-2.5 px-3">Total & DP</th>
                <th class="py-2.5 px-3">Status</th>
                <th class="py-2.5 pl-3 text-right">Aksi Dispatch</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="b in recentBookings" :key="b.id" class="hover:bg-slate-50/70 transition">
                <td class="py-3 pr-3">
                  <div class="font-mono font-bold text-slate-900">{{ b.booking_code }}</div>
                  <div class="text-slate-600 font-medium text-[11px]">{{ b.customer_name }}</div>
                  <div class="text-slate-400 text-[10px]">Jadwal: {{ b.service_date }}</div>
                </td>
                <td class="py-3 px-3">
                  <div class="font-bold text-slate-800 truncate max-w-[180px]">{{ b.product_name }}</div>
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    Mitra: <span class="font-semibold text-slate-700">{{ b.partner_name }}</span>
                  </div>
                  <div v-if="b.assigned_driver_name" class="text-[10px] text-emerald-700 font-semibold mt-0.5">
                    Supir: {{ b.assigned_driver_name }} ({{ b.assigned_vehicle_plate || 'Unit Siap' }})
                  </div>
                </td>
                <td class="py-3 px-3 tabular-nums">
                  <div class="font-bold text-slate-900">{{ formatRupiah(b.total_price) }}</div>
                  <div class="text-[10px] text-slate-500">DP: {{ formatRupiah(b.dp_amount) }}</div>
                </td>
                <td class="py-3 px-3">
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold border"
                    :class="statusPillClass(b.status)"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(b.status)"></span>
                    {{ b.status_label }}
                  </span>
                </td>
                <td class="py-3 pl-3 text-right">
                  <button
                    @click="openDispatchModal(b)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-primary hover:text-primary bg-white text-slate-700 font-semibold text-[11px] transition shadow-2xs"
                  >
                    <span>Dispatch</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Dispatch Partner Modal -->
    <div
      v-if="selectedBookingForDispatch"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
    >
      <div class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-xl space-y-5">
        <div class="flex items-start justify-between border-b border-slate-100 pb-3">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Order Dispatching Engine</span>
            <h3 class="text-base font-extrabold text-slate-900 mt-0.5">
              Tugaskan Mitra & Supir — {{ selectedBookingForDispatch.booking_code }}
            </h3>
          </div>
          <button @click="closeDispatchModal" class="text-slate-400 hover:text-slate-700 text-sm font-bold">✕</button>
        </div>

        <form @submit.prevent="submitDispatch" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Pilih Mitra Transportasi (Prioritas Rating Tertinggi)</label>
            <select
              v-model="dispatchForm.partner_id"
              required
              class="w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-primary focus:ring-primary"
            >
              <option value="" disabled>-- Pilih Mitra Terverifikasi --</option>
              <option v-for="p in availablePartners" :key="p.id" :value="p.id">
                {{ p.business_name }} ({{ p.city }}) — ★ {{ p.average_rating.toFixed(1) }} • {{ p.completed_trips_count }} Trip
              </option>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Supir Bertugas</label>
              <input
                v-model="dispatchForm.assigned_driver_name"
                type="text"
                placeholder="Misal: Bpk. Ucok Siregar"
                class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Plat Nomor Armada</label>
              <input
                v-model="dispatchForm.assigned_vehicle_plate"
                type="text"
                placeholder="Misal: BK 1421 TZ (Innova)"
                class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp Supir (Tetap Disamarkan s/d H-1)</label>
            <input
              v-model="dispatchForm.assigned_driver_phone"
              type="text"
              placeholder="Misal: 081265001122"
              class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
            />
            <p class="text-[10px] text-slate-500 mt-1">
              🔒 Sesuai Pilar 3, nomor kontak supir baru dikirimkan ke tamu otomatis pada H-1 keberangkatan.
            </p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="closeDispatchModal"
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="dispatchForm.processing"
              class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold shadow-xs"
            >
              Simpan & Dispatch Mitra
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
