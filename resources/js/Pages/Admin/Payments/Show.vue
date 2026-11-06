<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  payment: Object,
});

const isRejectModalOpen = ref(false);

const rejectForm = useForm({
  reason: '',
});

const submitApprove = () => {
  if (confirm(`Setujui bukti transfer pembayaran ${props.payment.payment_code}? Pesanan akan langsung TERKONFIRMASI dan unit teralokasi.`)) {
    useForm({}).post(route('admin.payments.approve', props.payment.id));
  }
};

const submitReject = () => {
  rejectForm.post(route('admin.payments.reject', props.payment.id), {
    onSuccess: () => {
      isRejectModalOpen.value = false;
    },
  });
};

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};
</script>

<template>
  <AdminLayout>
    <Head :title="`Verifikasi Bayar ${payment.payment_code} — Admin Console`" />

    <div class="mb-6 flex items-center justify-between">
      <Link
        :href="route('admin.payments.index')"
        class="text-xs font-bold text-text-muted hover:text-primary flex items-center gap-1.5"
      >
        ← Kembali ke Daftar Pembayaran
      </Link>

      <span
        :class="{
          'bg-emerald-50 text-emerald-700 border-emerald-200': payment.status === 'VERIFIED',
          'bg-amber-50 text-amber-700 border-amber-200': payment.status === 'PAYMENT_REVIEW',
          'bg-rose-50 text-danger border-rose-200': payment.status === 'REJECTED',
        }"
        class="px-3 py-1 rounded-full text-xs font-bold border uppercase"
      >
        Status: {{ payment.status }}
      </span>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left: Proof Preview (2 Cols) -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <h2 class="text-base font-bold text-text-main border-b border-border pb-3">Bukti Transfer Pelanggan</h2>

          <div v-for="proof in payment.proofs" :key="proof.id" class="space-y-4">
            <!-- Proof Image -->
            <div class="rounded-xl overflow-hidden border border-border bg-slate-100 max-h-[500px] flex items-center justify-center p-2">
              <img
                :src="proof.file_path"
                alt="Bukti Transfer"
                class="max-h-[480px] w-auto object-contain rounded-lg"
              />
            </div>

            <div class="p-4 bg-surface-muted rounded-xl border border-border grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
              <div>
                <span class="text-text-muted block text-[11px]">Bank & Rekening Pengirim:</span>
                <strong class="text-text-main">{{ proof.bank_sender_name }}</strong>
                <span class="block text-slate-700">{{ proof.bank_sender_account }}</span>
              </div>
              <div>
                <span class="text-text-muted block text-[11px]">Rekening Tujuan:</span>
                <strong class="text-text-main">{{ proof.bank_destination_name }}</strong>
              </div>
              <div>
                <span class="text-text-muted block text-[11px]">Waktu Transfer:</span>
                <strong class="text-text-main">{{ proof.transferred_at || 'Tidak tercatat' }}</strong>
              </div>
            </div>

            <div v-if="proof.verification_note" class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-xs text-danger">
              <strong>Catatan Penolakan:</strong> {{ proof.verification_note }}
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Action Panel & Booking Summary (1 Col) -->
      <div class="space-y-6">
        <!-- Verification Action Panel -->
        <div class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <h3 class="text-base font-bold text-text-main border-b border-border pb-3">Aksi Verifikasi Admin</h3>

          <div class="space-y-2 text-xs">
            <span class="text-text-muted block font-semibold">Nominal Wajib DP:</span>
            <div class="text-2xl font-black text-primary">
              {{ formatRupiah(payment.amount) }}
            </div>
          </div>

          <div class="space-y-3 pt-2">
            <button
              v-if="payment.status !== 'VERIFIED'"
              @click="submitApprove"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2"
            >
              <span>✅</span>
              <span>Validasi & Konfirmasi Pesanan</span>
            </button>

            <button
              v-if="payment.status !== 'REJECTED'"
              @click="isRejectModalOpen = true"
              class="w-full bg-danger hover:bg-rose-700 text-white font-bold py-3 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2"
            >
              <span>❌</span>
              <span>Tolak Bukti Transfer</span>
            </button>

            <div v-if="payment.status === 'VERIFIED'" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 font-semibold text-center">
              ✓ Pembayaran telah diverifikasi oleh {{ payment.proofs[0]?.verified_by || 'Admin' }} pada {{ payment.paid_at }}.
            </div>
          </div>
        </div>

        <!-- Booking Summary Card -->
        <div class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-3 text-xs">
          <h3 class="text-sm font-bold text-text-main border-b border-border pb-2">Rincian Pemesanan</h3>

          <div class="space-y-2">
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Kode Booking:</span>
              <span class="font-mono font-bold text-primary text-sm">{{ payment.booking.booking_code }}</span>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Produk:</span>
              <strong class="text-text-main">{{ payment.booking.product_name }}</strong>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Wisatawan:</span>
              <span class="text-text-main font-bold">{{ payment.booking.customer_name }}</span>
              <span class="block text-text-muted text-[11px]">{{ payment.booking.customer_phone }}</span>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Mitra Vendor:</span>
              <span class="text-text-main">{{ payment.booking.partner_name }}</span>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Tgl Keberangkatan:</span>
              <span class="text-text-main font-bold">{{ payment.booking.service_start_date }}</span>
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
        <h3 class="text-lg font-bold text-text-main">Tolak Bukti Pembayaran</h3>
        <p class="text-xs text-text-muted leading-relaxed">
          Tuliskan alasan penolakan (misal: mutasi bank belum masuk, nominal transfer kurang, atau gambar buram).
        </p>

        <form @submit.prevent="submitReject" class="space-y-4">
          <div>
            <textarea
              v-model="rejectForm.reason"
              rows="4"
              placeholder="Contoh: Nominal transfer pada bukti struk tidak sesuai dengan tagihan DP Rp ..."
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
  </AdminLayout>
</template>

