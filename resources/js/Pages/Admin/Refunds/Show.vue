<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  refund: Object,
});

const isRejecting = ref(false);

const processForm = useForm({
  transfer_reference: '',
  note: '',
});

const rejectForm = useForm({
  reason: '',
});

const submitProcess = () => {
  processForm.post(route('admin.refunds.process', props.refund.id), {
    preserveScroll: true,
  });
};

const submitReject = () => {
  rejectForm.post(route('admin.refunds.reject', props.refund.id), {
    preserveScroll: true,
  });
};

</script>

<template>
  <AdminLayout>
    <Head :title="`Proses Refund — Booking ${refund.booking.booking_code}`" />

    <div class="mb-6 flex items-center justify-between">
      <Link
        :href="route('admin.refunds.index')"
        class="inline-flex items-center gap-2 text-xs font-bold text-text-muted hover:text-primary transition"
      >
        <span>← Kembali ke Antrean Refund</span>
      </Link>

      <span
        :class="{
          'bg-amber-100 text-amber-900 border-amber-300': refund.status === 'PENDING',
          'bg-emerald-100 text-emerald-900 border-emerald-300': refund.status === 'PROCESSED',
          'bg-rose-100 text-danger border-rose-300': refund.status === 'REJECTED',
        }"
        class="px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wider"
      >
        Status: {{ refund.status }}
      </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left 2 Cols: Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Target Bank Account Box -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <div class="flex items-center gap-2 border-b border-border pb-3">
            <span class="text-xl">🏦</span>
            <h2 class="text-base font-bold text-text-main">Rekening Tujuan Transfer Pengembalian Dana</h2>
          </div>

          <div class="bg-surface-muted p-5 rounded-xl border border-border space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <span class="text-[10px] text-text-muted uppercase font-bold block mb-1">Bank Penerima:</span>
                <span class="text-base font-bold text-text-main">{{ refund.bank_name }}</span>
              </div>
              <div>
                <span class="text-[10px] text-text-muted uppercase font-bold block mb-1">Nomor Rekening:</span>
                <span class="text-lg font-mono font-black text-slate-800 select-all tracking-wide">
                  {{ refund.bank_account_number }}
                </span>
              </div>
            </div>

            <div class="pt-2 border-t border-border">
              <span class="text-[10px] text-text-muted uppercase font-bold block mb-1">Atas Nama Rekening:</span>
              <span class="text-sm font-bold text-text-main">{{ refund.bank_account_holder }}</span>
            </div>
          </div>

          <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1">
            <div class="font-bold">Alasan Pembatalan dari Pelanggan:</div>
            <p class="italic text-slate-700">"{{ refund.reason }}"</p>
          </div>
        </div>

        <!-- Associated Booking Snapshot -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4 text-xs">
          <h2 class="text-base font-bold text-text-main border-b border-border pb-3">Data Pemesanan & Nilai Transaksi</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <span class="text-text-muted block mb-0.5">Kode Booking:</span>
              <span class="font-mono font-bold text-text-main text-sm">{{ refund.booking.booking_code }}</span>
            </div>
            <div>
              <span class="text-text-muted block mb-0.5">Produk:</span>
              <span class="font-bold text-text-main">{{ refund.booking.product_name }}</span>
            </div>
            <div>
              <span class="text-text-muted block mb-0.5">Nama Pelanggan:</span>
              <span class="font-bold text-text-main">{{ refund.booking.customer_name }}</span> ({{ refund.booking.customer_phone }})
            </div>
            <div>
              <span class="text-text-muted block mb-0.5">Mitra Penyedia:</span>
              <span class="font-bold text-text-main">{{ refund.booking.partner_name || 'Platform Internal' }}</span>
            </div>
            <div>
              <span class="text-text-muted block mb-0.5">Total Harga Booking:</span>
              <span class="font-bold text-text-main">{{ formatRupiah(refund.booking.total_price) }}</span>
            </div>
            <div>
              <span class="text-text-muted block mb-0.5">Total Dana Terbayar (DP):</span>
              <span class="font-black text-emerald-700 text-sm">{{ formatRupiah(refund.booking.total_paid) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 1 Col: Execution Panel -->
      <div class="space-y-6">
        <!-- Amount Summary -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider border-b border-border pb-2">
            Ringkasan Pengembalian
          </h3>

          <div class="space-y-2 text-xs">
            <div class="flex justify-between">
              <span class="text-text-muted">Total Dibayar:</span>
              <span class="font-bold text-text-main">{{ formatRupiah(refund.booking.total_paid) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-text-muted">Kebijakan Refund:</span>
              <span class="font-bold text-emerald-700">{{ refund.calculated_refund_percentage }}%</span>
            </div>
            <div class="flex justify-between items-center pt-3 border-t border-border font-bold">
              <span class="text-text-main">Nominal Wajib Transfer:</span>
              <span class="text-xl font-black text-primary">{{ formatRupiah(refund.refund_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Action Card: Process or View Completed -->
        <div v-if="refund.status === 'PENDING'" class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4 text-xs">
          <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider border-b border-border pb-2">
            Tindakan Admin
          </h3>

          <form @submit.prevent="submitProcess" class="space-y-3">
            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Nomor Referensi Bank / Mutasi *</label>
              <input
                v-model="processForm.transfer_reference"
                type="text"
                placeholder="Contoh: REF-20261106-99281"
                class="w-full text-xs font-mono font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
              <span v-if="processForm.errors.transfer_reference" class="text-danger mt-1 block">
                {{ processForm.errors.transfer_reference }}
              </span>
            </div>

            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Catatan Tambahan (Opsional)</label>
              <textarea
                v-model="processForm.note"
                rows="2"
                placeholder="Catatan transfer admin..."
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="processForm.processing"
              class="w-full py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold transition flex items-center justify-center gap-2 shadow-xs disabled:opacity-50"
            >
              <span>💸</span>
              <span>{{ processForm.processing ? 'Memproses...' : 'Konfirmasi Transfer Refund Selesai' }}</span>
            </button>
          </form>

          <div class="pt-2 border-t border-border">
            <button
              v-if="!isRejecting"
              type="button"
              @click="isRejecting = true"
              class="w-full py-2 text-danger hover:bg-rose-50 rounded-xl font-bold transition"
            >
              Tolak Pengajuan Refund Ini
            </button>

            <!-- Rejection Form -->
            <form v-else @submit.prevent="submitReject" class="space-y-3 mt-2">
              <label class="block font-bold text-danger uppercase mb-1">Alasan Penolakan Refund *</label>
              <textarea
                v-model="rejectForm.reason"
                rows="2"
                placeholder="Jelaskan alasan penolakan..."
                class="w-full text-xs font-medium rounded-xl border-rose-300 focus:border-danger focus:ring-danger"
                required
              ></textarea>
              <span v-if="rejectForm.errors.reason" class="text-danger mt-1 block">{{ rejectForm.errors.reason }}</span>

              <div class="flex gap-2">
                <button
                  type="button"
                  @click="isRejecting = false"
                  class="flex-1 py-2 rounded-xl border border-border font-bold hover:bg-surface-muted"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="rejectForm.processing"
                  class="flex-1 py-2 rounded-xl bg-danger text-white font-bold hover:bg-rose-700 disabled:opacity-50"
                >
                  Tolak Refund
                </button>
              </div>
            </form>
          </div>
        </div>

        <div v-else class="p-5 bg-surface rounded-2xl border border-border shadow-xs text-xs space-y-2">
          <div class="font-bold text-text-main">Riwayat Pemrosesan Admin:</div>
          <p class="text-text-muted">
            Diproses oleh: <strong>{{ refund.processed_by || 'Admin' }}</strong> pada {{ refund.processed_at }}
          </p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

