<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  bookings: Object,
  counts: Object,
  partners: Array,
  filters: Object,
});

const searchInput = ref(props.filters.search || '');
const isReassignModalOpen = ref(false);
const selectedBooking = ref(null);

const reassignForm = useForm({
  partner_id: '',
  assigned_driver_name: '',
  assigned_driver_phone: '',
  assigned_vehicle_plate: '',
  reason: '',
});

const applyFilters = (newStatus = null, newType = null) => {
  router.get(
    route('admin.bookings.index'),
    {
      status: newStatus !== null ? newStatus : props.filters.status,
      type: newType !== null ? newType : props.filters.type,
      search: searchInput.value || undefined,
    },
    { preserveState: true, replace: true }
  );
};

const handleSearch = () => {
  applyFilters();
};

const resetFilters = () => {
  searchInput.value = '';
  router.get(route('admin.bookings.index'), {}, { replace: true });
};

const openReassignModal = (booking) => {
  selectedBooking.value = booking;
  reassignForm.partner_id = booking.partner?.id || '';
  reassignForm.assigned_driver_name = booking.assigned_driver_name || '';
  reassignForm.assigned_driver_phone = '';
  reassignForm.assigned_vehicle_plate = booking.assigned_vehicle_plate || '';
  reassignForm.reason = '';
  isReassignModalOpen.value = true;
};

const submitReassign = () => {
  reassignForm.post(route('admin.sla.reassign', selectedBooking.value.id), {
    onSuccess: () => {
      isReassignModalOpen.value = false;
      selectedBooking.value = null;
      reassignForm.reset();
    },
  });
};

const statusTabs = [
  { key: 'ALL', label: 'Semua Status', count: props.counts.all },
  { key: 'PENDING_APPROVAL', label: 'Menunggu Mitra', count: props.counts.pending_approval, color: 'text-amber-700 bg-amber-50' },
  { key: 'ASSIGNED_TO_PARTNER', label: 'Penugasan Platform', count: props.counts.assigned_to_partner, color: 'text-blue-700 bg-blue-50' },
  { key: 'AWAITING_PAYMENT', label: 'Menunggu DP', count: props.counts.awaiting_payment, color: 'text-indigo-700 bg-indigo-50' },
  { key: 'CONFIRMED', label: 'Terkonfirmasi', count: props.counts.confirmed, color: 'text-emerald-700 bg-emerald-50' },
  { key: 'MANUAL_REVIEW', label: 'Perlu Intervensi', count: props.counts.manual_review, color: 'text-rose-700 bg-rose-50' },
  { key: 'CANCELLED', label: 'Dibatalkan', count: props.counts.cancelled, color: 'text-slate-600 bg-slate-100' },
];
</script>

<template>
  <AdminLayout>
    <Head title="Semua Pesanan Marketplace — Admin Console" />

    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-black uppercase tracking-wider text-primary bg-primary/10 px-2.5 py-0.5 rounded-full">
              Pusat Manajemen Operasional
            </span>
          </div>
          <h1 class="text-2xl font-black text-text-main mt-1.5">Semua Pesanan Marketplace</h1>
          <p class="text-xs text-text-muted mt-0.5">
            Monitoring sentral seluruh transaksi paket wisata dan rental mobil se-Sumatera Utara, status SLA mitra, serta penugasan armada.
          </p>
        </div>

        <!-- Quick Summary Badges -->
        <div class="flex flex-wrap items-center gap-2">
          <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs">
            <span class="text-slate-400 block text-[10px] uppercase font-bold">Total Transaksi</span>
            <span class="font-black text-slate-800 text-sm">{{ counts.all }} Pesanan</span>
          </div>
          <div class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs">
            <span class="text-emerald-600 block text-[10px] uppercase font-bold">Rental Mobil</span>
            <span class="font-black text-emerald-800 text-sm">{{ counts.vehicle_count }} Unit</span>
          </div>
          <div class="px-3.5 py-2 rounded-xl bg-blue-50 border border-blue-200 text-xs">
            <span class="text-blue-600 block text-[10px] uppercase font-bold">Paket Wisata</span>
            <span class="font-black text-blue-800 text-sm">{{ counts.tour_count }} Paket</span>
          </div>
        </div>
      </div>

      <!-- Controls: Status Tabs, Category Filter & Search Bar -->
      <div class="bg-surface rounded-2xl p-4 border border-border shadow-xs space-y-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
          <button
            v-for="tab in statusTabs"
            :key="tab.key"
            type="button"
            @click="applyFilters(tab.key, null)"
            :class="[
              'px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer tactile-press',
              filters.status === tab.key
                ? 'bg-primary text-white shadow-xs'
                : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
            ]"
          >
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.count !== undefined"
              :class="[
                'px-2 py-0.5 rounded-full text-[10px] font-black',
                filters.status === tab.key ? 'bg-white/20 text-white' : (tab.color || 'bg-slate-100 text-slate-700')
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>

        <div class="pt-3 border-t border-border flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
          <!-- Type Filter Pills -->
          <div class="flex items-center gap-1.5 bg-surface-muted p-1 rounded-xl border border-border self-start">
            <button
              type="button"
              @click="applyFilters(null, 'ALL')"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                filters.type === 'ALL' || !filters.type
                  ? 'bg-white text-text-main shadow-2xs font-extrabold'
                  : 'text-text-muted hover:text-text-main'
              ]"
            >
              Semua Jenis
            </button>
            <button
              type="button"
              @click="applyFilters(null, 'VEHICLE')"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5',
                filters.type === 'VEHICLE'
                  ? 'bg-white text-text-main shadow-2xs font-extrabold'
                  : 'text-text-muted hover:text-text-main'
              ]"
            >
              <span>🚗 Rental Mobil</span>
              <span class="text-[10px] px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold">
                {{ counts.vehicle_count }}
              </span>
            </button>
            <button
              type="button"
              @click="applyFilters(null, 'TOUR')"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5',
                filters.type === 'TOUR'
                  ? 'bg-white text-text-main shadow-2xs font-extrabold'
                  : 'text-text-muted hover:text-text-main'
              ]"
            >
              <span>🗺️ Paket Tur</span>
              <span class="text-[10px] px-1.5 py-0.2 bg-blue-100 text-blue-800 rounded font-bold">
                {{ counts.tour_count }}
              </span>
            </button>
          </div>

          <!-- Search Input Form -->
          <form @submit.prevent="handleSearch" class="flex items-center gap-2">
            <div class="relative w-full sm:w-80">
              <input
                v-model="searchInput"
                type="text"
                placeholder="Cari kode booking, customer, mitra..."
                class="w-full text-xs pl-9 pr-8 py-2 rounded-xl border border-border bg-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
              />
              <svg class="w-4 h-4 text-text-muted absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <button
                v-if="searchInput"
                type="button"
                @click="resetFilters"
                class="absolute right-2.5 top-2.5 text-text-muted hover:text-text-main text-xs"
              >
                ✕
              </button>
            </div>
            <button
              type="submit"
              class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition shrink-0 tactile-press"
            >
              Cari
            </button>
          </form>
        </div>
      </div>

      <!-- Bookings Table Section -->
      <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
        <div class="p-5 border-b border-border flex items-center justify-between">
          <h2 class="text-sm font-bold text-text-main">
            Daftar Pesanan Terfilter ({{ bookings.total }} ditemukan)
          </h2>
          <span class="text-xs text-text-muted">Halaman {{ bookings.current_page }} dari {{ bookings.last_page }}</span>
        </div>

        <div v-if="bookings.data.length === 0" class="p-16 text-center">
          <div class="w-16 h-16 rounded-2xl bg-surface-muted text-text-muted flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
          </div>
          <h3 class="text-sm font-bold text-text-main">Tidak ada data pesanan</h3>
          <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">
            Tidak ditemukan transaksi yang cocok dengan kriteria filter atau kata kunci pencarian Anda.
          </p>
          <button
            v-if="filters.status !== 'ALL' || filters.type !== 'ALL' || filters.search"
            @click="resetFilters"
            class="mt-4 px-4 py-2 bg-primary/10 text-primary text-xs font-bold rounded-xl hover:bg-primary/20 transition"
          >
            Reset Semua Filter
          </button>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-border bg-surface-muted text-[10px] font-black uppercase tracking-wider text-text-muted">
                <th class="py-3 px-4">Kode & Tanggal</th>
                <th class="py-3 px-4">Tipe & Produk</th>
                <th class="py-3 px-4">Pemesan (Customer)</th>
                <th class="py-3 px-4">Mitra Pelaksana</th>
                <th class="py-3 px-4">Total & DP (30%)</th>
                <th class="py-3 px-4">Status Pesanan</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr
                v-for="b in bookings.data"
                :key="b.id"
                class="hover:bg-surface-muted/60 transition group"
              >
                <!-- Booking Code & Timestamp -->
                <td class="py-4 px-4 align-top">
                  <div class="font-mono font-bold text-slate-900 group-hover:text-primary transition">
                    {{ b.booking_code }}
                  </div>
                  <div class="text-[11px] text-text-muted mt-0.5">
                    Dibuat: {{ b.created_at }}
                  </div>
                  <div v-if="b.service_start_date" class="text-[11px] text-slate-600 font-medium mt-1 flex items-center gap-1">
                    <span>📅 Mulai:</span>
                    <span class="font-bold text-slate-800">{{ b.service_start_date }}</span>
                  </div>
                </td>

                <!-- Type & Product Snapshot -->
                <td class="py-4 px-4 align-top max-w-[220px]">
                  <div class="flex items-center gap-1.5 mb-1">
                    <span
                      v-if="b.product_type === 'VEHICLE'"
                      class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200/80 rounded-md text-[10px] font-bold inline-flex items-center gap-1"
                    >
                      🚗 Rental Kendaraan
                    </span>
                    <span
                      v-else
                      class="px-2 py-0.5 bg-blue-50 text-blue-800 border border-blue-200/80 rounded-md text-[10px] font-bold inline-flex items-center gap-1"
                    >
                      🗺️ Paket Tur
                    </span>
                  </div>
                  <div class="font-bold text-text-main line-clamp-2">
                    {{ b.product_name }}
                  </div>
                  <div v-if="b.assigned_vehicle_plate" class="text-[10px] font-mono font-bold text-slate-700 mt-1 bg-slate-100 px-1.5 py-0.5 rounded inline-block">
                    Pelat: {{ b.assigned_vehicle_plate }}
                  </div>
                </td>

                <!-- Customer Info -->
                <td class="py-4 px-4 align-top">
                  <div class="font-bold text-text-main">{{ b.customer?.name || 'Customer' }}</div>
                  <div class="text-[11px] text-text-muted mt-0.5">{{ b.customer?.phone || '-' }}</div>
                  <div class="text-[10px] text-slate-400 mt-0.5">{{ b.customer?.email }}</div>
                </td>

                <!-- Partner & Reassign Info -->
                <td class="py-4 px-4 align-top max-w-[200px]">
                  <div class="font-bold text-text-main flex items-center gap-1">
                    <span class="truncate">{{ b.partner?.name }}</span>
                  </div>
                  <div v-if="b.partner?.city" class="text-[10px] text-text-muted mt-0.5">
                    📍 {{ b.partner.city }}
                  </div>

                  <!-- Reassigned Badge if applicable -->
                  <div
                    v-if="b.is_reassigned"
                    class="mt-1.5 text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded px-1.5 py-0.5 inline-block"
                  >
                    🔄 Dialihkan dari: {{ b.original_partner_name }}
                  </div>

                  <div v-if="b.assigned_driver_name" class="text-[10px] text-emerald-700 font-semibold mt-1">
                    Supir: {{ b.assigned_driver_name }}
                  </div>
                </td>

                <!-- Financials -->
                <td class="py-4 px-4 align-top">
                  <div class="font-extrabold text-slate-900 tabular-nums">
                    {{ formatRupiah(b.total_price) }}
                  </div>
                  <div class="text-[10px] text-text-muted mt-0.5 tabular-nums">
                    DP: <span class="font-bold text-slate-700">{{ formatRupiah(b.dp_amount) }}</span>
                  </div>
                  <div class="mt-1">
                    <span
                      v-if="b.payment_status === 'PAID'"
                      class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                    >
                      Lunas
                    </span>
                    <span
                      v-else-if="b.payment_status === 'PARTIALLY_PAID'"
                      class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"
                    >
                      DP Terbayar
                    </span>
                    <span
                      v-else
                      class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"
                    >
                      Belum Bayar
                    </span>
                  </div>
                </td>

                <!-- Booking Status & SLA -->
                <td class="py-4 px-4 align-top">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider inline-block',
                      b.booking_status === 'CONFIRMED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                      b.booking_status === 'PENDING_APPROVAL' ? 'bg-amber-50 text-amber-800 border border-amber-200 animate-pulse' :
                      b.booking_status === 'ASSIGNED_TO_PARTNER' ? 'bg-blue-50 text-blue-800 border border-blue-200' :
                      b.booking_status === 'AWAITING_PAYMENT' ? 'bg-indigo-50 text-indigo-800 border border-indigo-200' :
                      b.booking_status === 'MANUAL_REVIEW' ? 'bg-rose-50 text-rose-700 border border-rose-200 font-black' :
                      'bg-slate-100 text-slate-700 border border-slate-200'
                    ]"
                  >
                    {{ b.booking_status_label }}
                  </span>

                  <!-- SLA Countdown for Pending Status -->
                  <div
                    v-if="b.remaining_sla_hours !== null"
                    :class="[
                      'text-[10px] font-bold mt-1.5 flex items-center gap-1',
                      b.is_sla_expired ? 'text-rose-600' : 'text-amber-700'
                    ]"
                  >
                    <span>⏱️</span>
                    <span v-if="b.is_sla_expired">SLA 24h Lewat!</span>
                    <span v-else>Sisa SLA: {{ b.remaining_sla_hours }} jam</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="py-4 px-4 align-top text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <Link
                      :href="route('admin.bookings.show', b.id)"
                      class="px-3 py-1.5 rounded-lg bg-surface border border-border text-slate-700 hover:text-primary hover:border-primary/50 text-xs font-bold transition tactile-press"
                    >
                      Detail →
                    </Link>

                    <button
                      type="button"
                      @click="openReassignModal(b)"
                      title="Alihkan / Tugaskan ke Mitra Baru"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-primary/10 text-slate-600 hover:text-primary transition tactile-press cursor-pointer"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="bookings.links && bookings.links.length > 3" class="p-4 border-t border-border flex items-center justify-center gap-1">
          <Link
            v-for="(link, i) in bookings.links"
            :key="i"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition',
              link.active ? 'bg-primary text-white font-bold' : 'text-text-muted hover:text-text-main hover:bg-surface-muted',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
          />
        </div>
      </div>
    </div>

    <!-- Modal Reassign / Alihkan Mitra -->
    <div
      v-if="isReassignModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-2xs"
    >
      <div class="bg-surface rounded-2xl border border-border shadow-2xl max-w-lg w-full p-6 animate-scale-up">
        <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
          <div class="flex items-center gap-2">
            <span class="text-lg">🔄</span>
            <div>
              <h3 class="text-sm font-black text-text-main">Alihkan / Tugaskan Pesanan ke Mitra Baru</h3>
              <p class="text-[11px] text-text-muted">Kode: <span class="font-mono font-bold text-text-main">{{ selectedBooking?.booking_code }}</span></p>
            </div>
          </div>
          <button
            @click="isReassignModalOpen = false"
            class="text-text-muted hover:text-text-main text-sm font-bold p-1"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="submitReassign" class="space-y-4">
          <!-- Pilih Mitra Baru -->
          <div>
            <label class="block text-xs font-bold text-text-main mb-1">
              Pilih Mitra Rental / Vendor Baru <span class="text-rose-500">*</span>
            </label>
            <select
              v-model="reassignForm.partner_id"
              required
              class="w-full text-xs p-2.5 rounded-xl border border-border bg-surface focus:outline-none focus:border-primary"
            >
              <option value="" disabled>-- Pilih Mitra Terverifikasi --</option>
              <option
                v-for="p in partners"
                :key="p.id"
                :value="p.id"
              >
                {{ p.business_name }} ({{ p.city }}) — ★ {{ p.average_rating || '5.0' }}
              </option>
            </select>
          </div>

          <!-- Opsional Alokasi Supir & Pelat Mobil -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-text-muted mb-1">Nama Driver (Opsional)</label>
              <input
                v-model="reassignForm.assigned_driver_name"
                type="text"
                placeholder="Contoh: Ucok Siregar"
                class="w-full text-xs p-2.5 rounded-xl border border-border bg-surface focus:outline-none focus:border-primary"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-text-muted mb-1">Pelat Nomor Kendaraan</label>
              <input
                v-model="reassignForm.assigned_vehicle_plate"
                type="text"
                placeholder="Contoh: BK 1234 ABC"
                class="w-full text-xs p-2.5 rounded-xl border border-border bg-surface focus:outline-none focus:border-primary"
              />
            </div>
          </div>

          <!-- Alasan Pengalihan -->
          <div>
            <label class="block text-xs font-bold text-text-main mb-1">
              Catatan / Alasan Pengalihan
            </label>
            <textarea
              v-model="reassignForm.reason"
              rows="2"
              placeholder="Contoh: Permintaan mitra sebelumnya penuh, dialihkan ke mitra terdekat di Berastagi."
              class="w-full text-xs p-2.5 rounded-xl border border-border bg-surface focus:outline-none focus:border-primary"
            ></textarea>
          </div>

          <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
            <button
              type="button"
              @click="isReassignModalOpen = false"
              class="px-4 py-2 text-xs font-bold text-text-muted hover:bg-surface-muted rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="reassignForm.processing || !reassignForm.partner_id"
              class="px-5 py-2 text-xs font-bold bg-primary text-white rounded-xl hover:bg-primary-dark transition disabled:opacity-50 tactile-press"
            >
              {{ reassignForm.processing ? 'Menugaskan...' : 'Tugaskan ke Mitra Baru' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

