<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  booking: Object,
  partners: Array,
});

const isReassignModalOpen = ref(false);

const reassignForm = useForm({
  partner_id: props.booking.partner?.id || '',
  assigned_driver_name: props.booking.assigned_driver_name || '',
  assigned_driver_phone: props.booking.assigned_driver_phone || '',
  assigned_vehicle_plate: props.booking.assigned_vehicle_plate || '',
  reason: '',
});

const submitReassign = () => {
  reassignForm.post(route('admin.sla.reassign', props.booking.id), {
    onSuccess: () => {
      isReassignModalOpen.value = false;
      reassignForm.reset();
    },
  });
};
</script>

<template>
  <AdminLayout>
    <Head :title="`Detail Pesanan ${booking.booking_code} — Admin Console`" />

    <div class="space-y-6 max-w-6xl mx-auto">
      <!-- Breadcrumb & Top Bar -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs">
          <Link
            :href="route('admin.bookings.index')"
            class="text-text-muted hover:text-primary font-semibold flex items-center gap-1 transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Semua Pesanan</span>
          </Link>
          <span class="text-text-muted">/</span>
          <span class="font-mono font-bold text-text-main">{{ booking.booking_code }}</span>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="isReassignModalOpen = true"
            class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-primary-dark transition tactile-press flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span>Alihkan / Tugaskan Mitra</span>
          </button>
        </div>
      </div>

      <!-- Main Status Card -->
      <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-border">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono font-black text-xl text-text-main tracking-tight">
                {{ booking.booking_code }}
              </span>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider',
                  booking.booking_status === 'CONFIRMED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                  booking.booking_status === 'PENDING_APPROVAL' ? 'bg-amber-50 text-amber-800 border border-amber-200 animate-pulse' :
                  booking.booking_status === 'ASSIGNED_TO_PARTNER' ? 'bg-blue-50 text-blue-800 border border-blue-200' :
                  booking.booking_status === 'AWAITING_PAYMENT' ? 'bg-indigo-50 text-indigo-800 border border-indigo-200' :
                  booking.booking_status === 'MANUAL_REVIEW' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                  'bg-slate-100 text-slate-700 border border-slate-200'
                ]"
              >
                {{ booking.booking_status_label }}
              </span>
            </div>
            <p class="text-xs text-text-muted mt-1">
              Dibuat pada {{ booking.created_at }} WIB • Jadwal Layanan: <span class="font-bold text-text-main">{{ booking.service_start_date }}</span>
            </p>
          </div>

          <!-- Quick State Dimensions -->
          <div class="flex items-center gap-3">
            <div class="text-right">
              <div class="text-[10px] uppercase font-bold text-text-muted">Status Pembayaran</div>
              <div class="text-xs font-bold text-text-main mt-0.5">
                <span
                  v-if="booking.payment_status === 'PAID'"
                  class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full"
                >
                  Lunas
                </span>
                <span
                  v-else-if="booking.payment_status === 'PARTIALLY_PAID'"
                  class="text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full"
                >
                  DP Terbayar
                </span>
                <span
                  v-else
                  class="text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-0.5 rounded-full"
                >
                  Belum Ada Pembayaran
                </span>
              </div>
            </div>

            <div class="h-8 w-px bg-border"></div>

            <div class="text-right">
              <div class="text-[10px] uppercase font-bold text-text-muted">Status Inventori</div>
              <div class="text-xs font-bold text-text-main mt-0.5">
                <span class="text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                  {{ booking.inventory_status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reassignment Alert (If applicable) -->
        <div
          v-if="booking.is_reassigned"
          class="mt-4 p-4 rounded-xl bg-blue-50/80 border border-blue-200 text-xs text-blue-900 flex items-start gap-3"
        >
          <span class="text-lg">ℹ️</span>
          <div>
            <div class="font-bold">Pesanan ini telah dialihkan oleh sistem / admin.</div>
            <div class="text-blue-800 text-[11px] mt-0.5">
              Mitra Pemilik Awal: <span class="font-bold">{{ booking.original_partner?.name }} ({{ booking.original_partner?.city }})</span>
              <span v-if="booking.rejection_reason"> • Alasan: "{{ booking.rejection_reason }}"</span>
            </div>
          </div>
        </div>

        <!-- SLA Countdown Banner if active -->
        <div
          v-if="booking.remaining_sla_hours !== null"
          :class="[
            'mt-4 p-4 rounded-xl text-xs flex items-center justify-between',
            booking.is_sla_expired ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-amber-50 border border-amber-200 text-amber-900'
          ]"
        >
          <div class="flex items-center gap-2">
            <span class="text-base">⏱️</span>
            <div>
              <span class="font-bold">Batas Waktu Konfirmasi Mitra (SLA 24 Jam):</span>
              <span v-if="booking.is_sla_expired" class="font-black text-rose-700 ml-1">
                Waktu habis! Segera hubungi mitra atau alihkan ke mitra cadangan.
              </span>
              <span v-else class="font-bold ml-1">
                Tersisa {{ booking.remaining_sla_hours }} jam lagi sebelum sistem menandai timeout.
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 2-Columns: Details Left & Right -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Product, Vehicle/Tour & Customer Snapshot -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Product Snapshot Card -->
          <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-border pb-3">
              <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
                <span>📦</span>
                <span>Snapshot Produk Layanan</span>
              </h3>
              <span
                v-if="booking.product_snapshot?.product_type === 'VEHICLE'"
                class="px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-md text-[10px] font-bold"
              >
                🚗 Rental Mobil
              </span>
              <span
                v-else
                class="px-2.5 py-0.5 bg-blue-50 text-blue-800 border border-blue-200 rounded-md text-[10px] font-bold"
              >
                🗺️ Paket Wisata
              </span>
            </div>

            <div>
              <h4 class="text-base font-extrabold text-text-main">
                {{ booking.product_snapshot?.name || 'Produk Wisata' }}
              </h4>
              <p class="text-xs text-text-muted mt-1">
                {{ booking.product_snapshot?.short_description || '-' }}
              </p>
            </div>

            <!-- Specific Vehicle Rental Details -->
            <div v-if="booking.vehicle_detail" class="bg-surface-muted/60 p-4 rounded-xl border border-border space-y-3">
              <div class="text-xs font-bold text-text-main uppercase tracking-wider text-slate-500">
                Informasi Rental Mobil & Penjemputan
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                  <span class="text-text-muted text-[11px] block">Titik Penjemputan:</span>
                  <span class="font-bold text-text-main">{{ booking.vehicle_detail.pickup_location || 'Bandara Kualanamu / Hotel' }}</span>
                </div>
                <div>
                  <span class="text-text-muted text-[11px] block">Titik Pengantaran:</span>
                  <span class="font-bold text-text-main">{{ booking.vehicle_detail.dropoff_location || 'Sesuai Rute Perjalanan' }}</span>
                </div>
              </div>
              <div v-if="booking.vehicle_detail.driver_notes" class="text-xs pt-2 border-t border-border">
                <span class="text-text-muted text-[11px] block">Catatan Tambahan Customer:</span>
                <span class="text-slate-700 italic">"{{ booking.vehicle_detail.driver_notes }}"</span>
              </div>
            </div>

            <!-- Specific Tour Details -->
            <div v-if="booking.tour_detail" class="bg-surface-muted/60 p-4 rounded-xl border border-border space-y-3">
              <div class="text-xs font-bold text-text-main uppercase tracking-wider text-slate-500">
                Informasi Paket Tur
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                  <span class="text-text-muted text-[11px] block">Jumlah Peserta:</span>
                  <span class="font-bold text-text-main">{{ booking.tour_detail.participant_count }} Orang</span>
                </div>
                <div>
                  <span class="text-text-muted text-[11px] block">Permintaan Khusus:</span>
                  <span class="font-bold text-text-main">{{ booking.tour_detail.special_requests || 'Tidak ada' }}</span>
                </div>
              </div>
            </div>

            <!-- Assigned Driver & Fleet Plate Info -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
              <div>
                <span class="text-slate-500 text-[11px] block">Armada & Supir Ditugaskan:</span>
                <span class="font-bold text-slate-800 text-sm">
                  {{ booking.assigned_driver_name || 'Belum diisi oleh mitra' }}
                  <span v-if="booking.assigned_driver_phone" class="text-xs text-slate-500 font-normal">({{ booking.assigned_driver_phone }})</span>
                </span>
              </div>
              <div v-if="booking.assigned_vehicle_plate" class="text-right">
                <span class="text-slate-500 text-[11px] block">Pelat Nomor Kendaraan:</span>
                <span class="font-mono font-black text-slate-900 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                  {{ booking.assigned_vehicle_plate }}
                </span>
              </div>
            </div>
          </div>

          <!-- Customer & Partner Info Cards (Side by Side) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Customer Card -->
            <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs space-y-3">
              <h4 class="text-xs font-bold uppercase tracking-wider text-text-muted flex items-center gap-1.5">
                <span>👤</span>
                <span>Data Pemesan</span>
              </h4>
              <div>
                <div class="text-sm font-black text-text-main">{{ booking.customer?.name }}</div>
                <div class="text-xs text-text-muted mt-0.5">{{ booking.customer?.email }}</div>
                <div class="text-xs font-bold text-primary mt-1">{{ booking.customer?.phone || '-' }}</div>
              </div>
              <div v-if="booking.customer?.phone" class="pt-2 border-t border-border">
                <a
                  :href="`https://wa.me/${booking.customer.phone.replace(/[^0-9]/g, '')}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline"
                >
                  <span>💬 Hubungi via WhatsApp</span>
                </a>
              </div>
            </div>

            <!-- Partner Card -->
            <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs space-y-3">
              <h4 class="text-xs font-bold uppercase tracking-wider text-text-muted flex items-center gap-1.5">
                <span>🏢</span>
                <span>Mitra Pelaksana</span>
              </h4>
              <div>
                <div class="text-sm font-black text-text-main">{{ booking.partner?.name || 'Platform Curated' }}</div>
                <div class="text-xs text-text-muted mt-0.5">PIC: {{ booking.partner?.pic_name || '-' }} • 📍 {{ booking.partner?.city }}</div>
                <div class="text-xs font-bold text-slate-700 mt-1">Telp: {{ booking.partner?.phone || '-' }}</div>
              </div>
              <div v-if="booking.partner?.phone" class="pt-2 border-t border-border flex items-center justify-between">
                <a
                  :href="`https://wa.me/${booking.partner.phone.replace(/[^0-9]/g, '')}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline"
                >
                  <span>💬 WhatsApp PIC Mitra</span>
                </a>
                <span class="text-[11px] font-bold text-amber-600">★ {{ booking.partner?.rating || '5.0' }}</span>
              </div>
            </div>
          </div>

          <!-- Status Audit Trail Logs -->
          <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
              <span>📋</span>
              <span>Riwayat Perubahan Status (Audit Trail)</span>
            </h3>

            <div v-if="booking.histories.length === 0" class="text-xs text-text-muted py-4 text-center">
              Belum ada catatan riwayat status.
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="h in booking.histories"
                :key="h.id"
                class="p-3 rounded-xl bg-surface-muted/60 border border-border text-xs flex items-start justify-between gap-4"
              >
                <div>
                  <div class="font-semibold text-text-main">
                    {{ h.actor_name }}:
                    <span class="font-mono text-primary font-bold">{{ h.previous_status }}</span>
                    <span> → </span>
                    <span class="font-mono text-emerald-600 font-bold">{{ h.new_status }}</span>
                  </div>
                  <div v-if="h.reason" class="text-slate-600 text-[11px] mt-0.5">
                    "{{ h.reason }}"
                  </div>
                </div>
                <div class="text-[10px] text-text-muted shrink-0 text-right">
                  {{ h.created_at }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right 1 Col: Financials & Payment Proof -->
        <div class="space-y-6">
          <!-- Financial Breakdown Card -->
          <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
              <span>💰</span>
              <span>Rincian Finansial & Komisi</span>
            </h3>

            <div class="space-y-2 text-xs divide-y divide-border">
              <div class="flex items-center justify-between pb-2">
                <span class="text-text-muted">Total Nilai Transaksi:</span>
                <span class="font-black text-slate-900 text-sm tabular-nums">{{ formatRupiah(booking.total_price) }}</span>
              </div>
              <div class="flex items-center justify-between py-2">
                <span class="text-text-muted">Tagihan DP (30%):</span>
                <span class="font-bold text-primary tabular-nums">{{ formatRupiah(booking.dp_amount) }}</span>
              </div>
              <div class="flex items-center justify-between py-2">
                <span class="text-text-muted">Pelunasan di Lokasi (70%):</span>
                <span class="font-semibold text-slate-700 tabular-nums">{{ formatRupiah(booking.total_price - booking.dp_amount) }}</span>
              </div>
              <div class="flex items-center justify-between py-2 text-[11px]">
                <span class="text-text-muted">Komisi Platform ({{ booking.commission_percentage }}%):</span>
                <span class="font-bold text-emerald-700 tabular-nums">{{ formatRupiah(booking.platform_commission_amount) }}</span>
              </div>
              <div class="flex items-center justify-between pt-2">
                <span class="text-text-muted font-bold">Hak Mitra (Net Payout):</span>
                <span class="font-black text-slate-900 tabular-nums">{{ formatRupiah(booking.partner_payout_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Payment Proofs Card -->
          <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
              <span>🧾</span>
              <span>Bukti Transfer Pembayaran</span>
            </h3>

            <div v-if="booking.payments.length === 0" class="text-xs text-text-muted py-6 text-center">
              Belum ada pembayaran yang disubmit.
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="p in booking.payments"
                :key="p.id"
                class="p-4 rounded-xl bg-surface-muted/60 border border-border space-y-2 text-xs"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-text-main">{{ p.payment_type }}</span>
                  <span class="font-extrabold text-slate-900 tabular-nums">{{ formatRupiah(p.amount) }}</span>
                </div>
                <div class="text-[11px] text-text-muted">
                  Waktu: {{ p.created_at }}
                  <span v-if="p.bank_sender"> • Pengirim: {{ p.bank_sender }}</span>
                </div>

                <div v-if="p.proof_image_url" class="pt-2">
                  <a
                    :href="p.proof_image_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block rounded-lg overflow-hidden border border-border group relative max-h-48"
                  >
                    <img :src="p.proof_image_url" alt="Bukti Transfer" class="w-full object-cover group-hover:scale-105 transition" />
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[11px] font-bold">
                      Buka Gambar Penuh ↗
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>
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
              <h3 class="text-sm font-black text-text-main">Alihkan Pesanan ke Mitra Baru</h3>
              <p class="text-[11px] text-text-muted">Kode: <span class="font-mono font-bold text-text-main">{{ booking.booking_code }}</span></p>
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
          <div>
            <label class="block text-xs font-bold text-text-main mb-1">
              Pilih Mitra Vendor Baru <span class="text-rose-500">*</span>
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
              <label class="block text-[11px] font-semibold text-text-muted mb-1">Pelat Nomor</label>
              <input
                v-model="reassignForm.assigned_vehicle_plate"
                type="text"
                placeholder="Contoh: BK 1234 ABC"
                class="w-full text-xs p-2.5 rounded-xl border border-border bg-surface focus:outline-none focus:border-primary"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-text-main mb-1">
              Catatan / Alasan Pengalihan
            </label>
            <textarea
              v-model="reassignForm.reason"
              rows="2"
              placeholder="Contoh: Armada mitra awal sedang perbaikan, dialihkan ke mitra terdekat."
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

