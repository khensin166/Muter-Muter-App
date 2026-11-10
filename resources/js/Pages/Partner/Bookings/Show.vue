<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  booking: Object,
});

const isRejectModalOpen = ref(false);

const rejectForm = useForm({
  reason: '',
});

const submitApprove = () => {
  if (confirm(`Setujui ketersediaan pesanan ${props.booking.booking_code}? Tagihan DP dan hold inventory 24 jam akan diterbitkan.`)) {
    useForm({}).post(route('partner.bookings.approve', props.booking.id));
  }
};

const submitReject = () => {
  rejectForm.post(route('partner.bookings.reject', props.booking.id), {
    onSuccess: () => {
      isRejectModalOpen.value = false;
    },
  });
};

</script>

<template>
  <PartnerLayout>
    <Head :title="`Detail Pesanan ${booking.booking_code} — Mitra Vendor`" />

    <div class="p-6 max-w-6xl mx-auto space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex items-center justify-between">
        <Link
          :href="route('partner.bookings.index')"
          class="text-xs font-bold text-text-muted hover:text-primary flex items-center gap-1.5"
        >
          ← Kembali ke Kotak Masuk
        </Link>

        <span
          :class="{
            'bg-amber-100 text-amber-900 border-amber-300': booking.booking_status === 'PENDING_APPROVAL',
            'bg-purple-100 text-purple-900 border-purple-300': booking.booking_status === 'MANUAL_REVIEW',
            'bg-blue-100 text-blue-900 border-blue-300': booking.booking_status === 'AWAITING_PAYMENT',
            'bg-emerald-100 text-emerald-900 border-emerald-300': booking.booking_status === 'CONFIRMED',
            'bg-rose-100 text-danger border-rose-300': ['REJECTED', 'CANCELLED', 'EXPIRED'].includes(booking.booking_status),
          }"
          class="px-3 py-1 rounded-full text-xs font-bold border uppercase"
        >
          {{ booking.booking_status_label }}
        </span>
      </div>

      <!-- SLA Alert if Pending -->
      <div v-if="booking.booking_status === 'PENDING_APPROVAL'" class="p-5 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 flex items-center justify-between gap-4">
        <div>
          <span class="font-bold text-sm text-amber-800 block">⚡ SLA Konfirmasi Ketersediaan: 24 Jam</span>
          <p class="mt-0.5">Sisa waktu respons: <strong>{{ booking.remaining_sla_hours }} Jam lagi</strong>. Harap berikan konfirmasi sebelum batas waktu habis.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <button
            @click="submitApprove"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm"
          >
            ✓ Setujui Ketersediaan
          </button>
          <button
            @click="isRejectModalOpen = true"
            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-sm"
          >
            ✕ Tolak
          </button>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details Column (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
            <h2 class="text-base font-bold text-text-main border-b border-border pb-3">Informasi Pemesanan</h2>

            <div class="space-y-3 text-xs">
              <div>
                <span class="text-text-muted block font-semibold mb-0.5">Produk:</span>
                <strong class="text-base text-text-main">{{ booking.product_snapshot.name }}</strong>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                  <span class="text-text-muted block font-semibold mb-0.5">Tanggal Keberangkatan / Mulai:</span>
                  <span class="font-bold text-text-main">{{ booking.service_start_date }} (Jam {{ booking.service_start_time || '08:00' }})</span>
                </div>
                <div>
                  <span class="text-text-muted block font-semibold mb-0.5">Tanggal Selesai:</span>
                  <span class="font-bold text-text-main">{{ booking.service_end_date }}</span>
                </div>
              </div>

              <!-- Tour details -->
              <div v-if="booking.tour_detail" class="pt-3 border-t border-border space-y-2">
                <div>
                  <span class="text-text-muted block font-semibold mb-0.5">Jumlah Peserta:</span>
                  <strong class="text-text-main">{{ booking.tour_detail.participant_count }} Orang</strong>
                </div>
                <div v-if="booking.tour_detail.special_requests">
                  <span class="text-text-muted block font-semibold mb-0.5">Catatan Khusus Pelanggan:</span>
                  <p class="p-3 bg-surface-muted rounded-xl border border-border text-text-main">
                    {{ booking.tour_detail.special_requests }}
                  </p>
                </div>
              </div>

              <!-- Vehicle details -->
              <div v-if="booking.vehicle_detail" class="pt-3 border-t border-border space-y-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <span class="text-text-muted block font-semibold mb-0.5">Titik Penjemputan:</span>
                    <strong class="text-text-main">{{ booking.vehicle_detail.pickup_location }}</strong>
                  </div>
                  <div>
                    <span class="text-text-muted block font-semibold mb-0.5">Titik Pengembalian:</span>
                    <strong class="text-text-main">{{ booking.vehicle_detail.dropoff_location }}</strong>
                  </div>
                </div>
                <div v-if="booking.vehicle_detail.driver_notes">
                  <span class="text-text-muted block font-semibold mb-0.5">Catatan Tambahan untuk Driver:</span>
                  <p class="p-3 bg-surface-muted rounded-xl border border-border text-text-main">
                    {{ booking.vehicle_detail.driver_notes }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- History -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
            <h3 class="text-base font-bold text-text-main border-b border-border pb-3">Riwayat Status</h3>

            <div class="space-y-3 text-xs">
              <div v-for="h in booking.histories" :key="h.id" class="p-3 bg-surface-muted rounded-xl border border-border space-y-1">
                <div class="flex justify-between items-center">
                  <span class="font-bold text-primary">{{ h.actor_name }}</span>
                  <span class="text-[10px] text-text-muted">{{ h.created_at }}</span>
                </div>
                <span class="text-[11px] font-bold text-text-main">{{ h.previous_status }} → {{ h.new_status }}</span>
                <p class="text-text-muted text-[11px]">{{ h.reason }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Column (1 Col) -->
        <div class="space-y-6">
          <!-- Customer Profile Card -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-3">
            <h3 class="text-sm font-bold text-text-main border-b border-border pb-2">Data Wisatawan</h3>
            <div class="space-y-2 text-xs">
              <div>
                <span class="text-text-muted block font-semibold mb-0.5">Nama:</span>
                <strong class="text-text-main">{{ booking.customer.name }}</strong>
              </div>
              <div>
                <span class="text-text-muted block font-semibold mb-0.5">Nomor WhatsApp / HP:</span>
                <a :href="`https://wa.me/${booking.customer.phone}`" target="_blank" class="text-emerald-700 font-bold hover:underline">
                  {{ booking.customer.phone }} 💬
                </a>
              </div>
              <div>
                <span class="text-text-muted block font-semibold mb-0.5">Email:</span>
                <span class="text-text-main">{{ booking.customer.email }}</span>
              </div>
            </div>
          </div>

          <!-- Revenue Card -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-3">
            <h3 class="text-sm font-bold text-text-main border-b border-border pb-2">Estimasi Pendapatan Mitra</h3>
            <div class="space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-text-muted">Total Nilai Pesanan:</span>
                <span class="font-bold text-text-main">{{ formatRupiah(booking.total_price) }}</span>
              </div>
              <div class="flex justify-between text-text-muted text-[11px]">
                <span>Komisi Platform (10%):</span>
                <span>- {{ formatRupiah(booking.total_price * 0.1) }}</span>
              </div>
              <div class="flex justify-between text-primary font-bold pt-2 border-t border-border">
                <span>Net Payout Mitra:</span>
                <span class="text-base font-black">{{ formatRupiah(booking.partner_payout_amount) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div
      v-if="isRejectModalOpen"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-lg w-full p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-text-main">Tolak Ketersediaan Jadwal</h3>
        <p class="text-xs text-text-muted leading-relaxed">
          Berikan alasan penolakan (misal: unit kendaraan sedang dalam perawatan rutin atau kuota tour sudah penuh).
        </p>

        <form @submit.prevent="submitReject" class="space-y-4">
          <div>
            <textarea
              v-model="rejectForm.reason"
              rows="4"
              placeholder="Contoh: Seluruh armada pada tanggal tersebut sudah terisi penuh oleh rombongan lain..."
              class="w-full text-xs font-medium rounded-xl border-border focus:border-danger focus:ring-danger"
              required
            ></textarea>
            <span v-if="rejectForm.errors.reason" class="text-danger text-xs mt-1 block">
              {{ rejectForm.errors.reason }}
            </span>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="isRejectModalOpen = false"
              class="px-4 py-2 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="rejectForm.processing"
              class="px-5 py-2 rounded-xl bg-danger text-white text-xs font-bold hover:bg-rose-700 disabled:opacity-50"
            >
              {{ rejectForm.processing ? 'Menyimpan...' : 'Kirim Penolakan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </PartnerLayout>
</template>

