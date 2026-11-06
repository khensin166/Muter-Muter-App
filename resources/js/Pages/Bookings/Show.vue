<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
  booking: Object,
  bankAccount: Object,
});

const isUploadModalOpen = ref(false);
const proofForm = useForm({
  proof_file: null,
  bank_sender_name: '',
  bank_sender_account: '',
});

const isCancelModalOpen = ref(false);
const cancelForm = useForm({
  reason: '',
  bank_name: 'Bank Mandiri',
  bank_account_number: '',
  bank_account_holder: '',
});

const submitProof = () => {
  proofForm.post(route('bookings.payment-proof.store', props.booking.booking_code), {
    onSuccess: () => {
      isUploadModalOpen.value = false;
      proofForm.reset();
    },
  });
};

const submitCancel = () => {
  cancelForm.post(route('bookings.cancel', props.booking.booking_code), {
    onSuccess: () => {
      isCancelModalOpen.value = false;
      cancelForm.reset();
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

const steps = [
  { key: 'PENDING_APPROVAL', label: '1. Diajukan ke Mitra' },
  { key: 'AWAITING_PAYMENT', label: '2. Tagihan DP' },
  { key: 'PAYMENT_REVIEW', label: '3. Verifikasi Bukti' },
  { key: 'CONFIRMED', label: '4. Terkonfirmasi' },
];

const currentStepIndex = computed(() => {
  switch (props.booking.status) {
    case 'PENDING_APPROVAL':
    case 'MANUAL_REVIEW':
      return 0;
    case 'AWAITING_PAYMENT':
      return 1;
    case 'PAYMENT_REVIEW':
      return 2;
    case 'CONFIRMED':
    case 'IN_PROGRESS':
    case 'COMPLETED':
      return 3;
    default:
      return -1;
  }
});
</script>

<template>
  <AppLayout>
    <Head :title="`Detail Booking ${booking.booking_code} — Muter-Muter`" />

    <!-- Top Breadcrumb -->
    <div class="bg-surface-muted border-b border-border py-4 px-4 sm:px-6 lg:px-8">
      <div class="max-w-6xl mx-auto flex items-center justify-between text-xs">
        <div class="flex items-center gap-2 text-text-muted">
          <Link href="/" class="hover:text-primary">Beranda</Link>
          <span>/</span>
          <Link href="/my-bookings" class="hover:text-primary">Pesanan Saya</Link>
          <span>/</span>
          <span class="text-text-main font-semibold">{{ booking.booking_code }}</span>
        </div>

        <span
          :class="{
            'bg-amber-100 text-amber-900 border-amber-300': booking.status === 'PENDING_APPROVAL',
            'bg-purple-100 text-purple-900 border-purple-300': booking.status === 'MANUAL_REVIEW',
            'bg-blue-100 text-blue-900 border-blue-300': booking.status === 'AWAITING_PAYMENT',
            'bg-emerald-100 text-emerald-900 border-emerald-300': booking.status === 'CONFIRMED',
            'bg-rose-100 text-danger border-rose-300': ['REJECTED', 'CANCELLED', 'EXPIRED'].includes(booking.status),
          }"
          class="px-3 py-1 rounded-full font-bold border uppercase text-[11px]"
        >
          {{ booking.status_label }}
        </span>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Status Stepper Bar -->
      <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
        <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider mb-6">Status Progres Pemesanan</h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 relative">
          <div
            v-for="(step, idx) in steps"
            :key="step.key"
            class="flex flex-col items-center text-center relative z-10"
          >
            <div
              :class="[
                'w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold transition-all mb-2',
                idx < currentStepIndex
                  ? 'bg-emerald-600 text-white'
                  : (idx === currentStepIndex ? 'bg-primary text-white ring-4 ring-primary/20' : 'bg-surface-muted text-text-muted border border-border')
              ]"
            >
              <span v-if="idx < currentStepIndex">✓</span>
              <span v-else>{{ idx + 1 }}</span>
            </div>
            <span
              :class="idx <= currentStepIndex ? 'font-bold text-text-main' : 'text-text-muted'"
              class="text-xs"
            >
              {{ step.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Alert Banners depending on state -->
      <!-- Case 1: PENDING_APPROVAL (24h SLA) -->
      <div v-if="booking.status === 'PENDING_APPROVAL'" class="p-5 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 space-y-2">
        <div class="flex items-center gap-2 font-bold text-sm text-amber-800">
          <span>⏱️</span>
          <span>Menunggu Konfirmasi Ketersediaan dari Mitra Vendor</span>
        </div>
        <p class="leading-relaxed">
          Mitra memiliki batas waktu <strong>maksimal 24 jam</strong> untuk menyetujui pemesanan ini (Batas waktu: {{ booking.vendor_sla_deadline }}). Jika mitra tidak merespons hingga batas waktu habis, sistem akan otomatis mengalihkan pesanan ke tim Admin Muter-Muter untuk intervensi manual.
        </p>
      </div>

      <!-- Case 2: MANUAL_REVIEW (SLA Expired Escalated to Admin) -->
      <div v-if="booking.status === 'MANUAL_REVIEW'" class="p-5 bg-purple-50 rounded-2xl border border-purple-200 text-xs text-purple-900 space-y-2">
        <div class="flex items-center gap-2 font-bold text-sm text-purple-800">
          <span>🚨</span>
          <span>Intervensi Manual Admin Muter-Muter Sedang Berjalan</span>
        </div>
        <p class="leading-relaxed">
          Waktu respons mitra vendor telah melampaui batas SLA 24 jam. Tim operasional Admin Muter-Muter saat ini sedang menghubungi pihak penyedia secara langsung untuk memastikan armada/jadwal Anda terakomodasi.
        </p>
      </div>

      <!-- Case 3: AWAITING_PAYMENT (Vendor Approved -> 24h Inventory Hold & DP Bill) -->
      <div v-if="booking.status === 'AWAITING_PAYMENT'" class="p-6 bg-emerald-50 rounded-2xl border border-emerald-300 text-xs text-emerald-950 space-y-4">
        <div class="flex items-center justify-between border-b border-emerald-200 pb-3">
          <div class="flex items-center gap-2 font-bold text-base text-emerald-900">
            <span>🎉</span>
            <span>Ketersediaan Disetujui! Silakan Lakukan Pembayaran DP</span>
          </div>
          <span class="bg-emerald-600 text-white font-bold px-3 py-1 rounded-full text-xs">
            Hold 24 Jam Aktif
          </span>
        </div>

        <p class="leading-relaxed">
          Mitra telah mengonfirmasi ketersediaan paket/armada Anda. Jadwal telah <strong>di-hold sementara selama 24 jam</strong>. Segera transfer nominal DP ke rekening resmi platform di bawah ini agar pemesanan terkonfirmasi pasti.
        </p>

        <!-- Payment Instructions Box -->
        <div class="bg-white p-5 rounded-xl border border-emerald-200 grid grid-cols-1 sm:grid-cols-2 gap-6 shadow-xs">
          <div>
            <span class="text-text-muted block text-[11px] uppercase font-bold">Nominal Down Payment (DP):</span>
            <div class="text-2xl font-black text-primary mt-1">
              {{ formatRupiah(booking.dp_amount) }}
            </div>
            <span class="text-[11px] text-text-muted block mt-1">
              Sisa pelunasan {{ formatRupiah(booking.balance_amount) }} dibayarkan sebelum/saat hari keberangkatan.
            </span>
          </div>

          <div class="space-y-1 text-xs">
            <span class="text-text-muted block text-[11px] uppercase font-bold">Rekening Resmi Platform:</span>
            <div class="font-bold text-text-main text-sm">{{ bankAccount.bank_name }}</div>
            <div class="text-base font-mono font-bold text-slate-800 select-all">{{ bankAccount.account_number }}</div>
            <div class="text-xs text-text-muted">a/n {{ bankAccount.account_holder }}</div>
          </div>
        </div>

        <!-- Callout to WhatsApp or Web Upload -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
          <button
            @click="isUploadModalOpen = true"
            class="px-5 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-center transition flex items-center justify-center gap-2 shadow-sm"
          >
            <span>📤</span>
            <span>Upload Bukti Transfer DP di Web</span>
          </button>
          <a
            :href="`https://wa.me/6281234567890?text=Halo%20Admin%20Muter-Muter,%20saya%20sudah%20transfer%20DP%20untuk%20kode%20booking%20${booking.booking_code}`"
            target="_blank"
            class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-center transition flex items-center justify-center gap-2"
          >
            <span>💬</span>
            <span>Konfirmasi Pembayaran via WhatsApp</span>
          </a>
        </div>
      </div>

      <!-- Case 4: PAYMENT_REVIEW -->
      <div v-if="booking.status === 'PAYMENT_REVIEW'" class="p-5 bg-blue-50 rounded-2xl border border-blue-200 text-xs text-blue-950 space-y-2">
        <div class="flex items-center gap-2 font-bold text-sm text-blue-900">
          <span>🔍</span>
          <span>Bukti Pembayaran DP Sedang Diverifikasi Admin</span>
        </div>
        <p class="leading-relaxed">
          Terima kasih! Bukti transfer pembayaran DP Anda telah diterima dan saat ini sedang dalam proses verifikasi mutasi rekening oleh tim keuangan Muter-Muter. Pesanan akan segera berstatus <strong>Terkonfirmasi</strong> setelah verifikasi selesai.
        </p>
      </div>

      <!-- Case 5: CONFIRMED -->
      <div v-if="booking.status === 'CONFIRMED'" class="p-6 bg-emerald-50 rounded-2xl border border-emerald-300 text-xs text-emerald-950 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 font-bold text-base text-emerald-900">
            <span>✅</span>
            <span>Pesanan Terkonfirmasi & Unit Teralokasi Pasti!</span>
          </div>
          <span class="bg-emerald-600 text-white font-bold px-3 py-1 rounded-full text-xs">
            Unit Dijamin
          </span>
        </div>
        <p class="leading-relaxed">
          Down Payment (DP) senilai <strong>{{ formatRupiah(booking.total_paid) }}</strong> telah terverifikasi. Armada atau pemandu wisata Anda resmi dialokasikan. Sisa pelunasan sebesar <strong>{{ formatRupiah(booking.balance_amount) }}</strong> dibayarkan paling lambat saat hari keberangkatan.
        </p>
      </div>

      <!-- Case 6: REFUND_PENDING -->
      <div v-if="booking.status === 'REFUND_PENDING'" class="p-6 bg-amber-50 rounded-2xl border border-amber-300 text-xs text-amber-950 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 font-bold text-base text-amber-900">
            <span>⏳</span>
            <span>Pengajuan Refund Sedang Diproses Admin</span>
          </div>
          <span class="bg-amber-600 text-white font-bold px-3 py-1 rounded-full text-xs">
            Menunggu Transfer
          </span>
        </div>
        <p class="leading-relaxed">
          Pesanan ini telah dibatalkan dan pengembalian dana senilai
          <strong class="text-amber-950 text-sm font-black">{{ formatRupiah(booking.latest_refund?.refund_amount || booking.estimated_refund_amount) }}</strong>
          sedang dalam antrean transfer ke rekening <strong>{{ booking.latest_refund?.bank_name }} - {{ booking.latest_refund?.bank_account_number }} a/n {{ booking.latest_refund?.bank_account_holder }}</strong>.
        </p>
      </div>

      <!-- Case 7: REFUNDED -->
      <div v-if="booking.status === 'REFUNDED'" class="p-6 bg-slate-50 rounded-2xl border border-slate-300 text-xs text-slate-800 space-y-2">
        <div class="flex items-center gap-2 font-bold text-base text-slate-900">
          <span>💸</span>
          <span>Pengembalian Dana (Refund) Telah Ditransfer</span>
        </div>
        <p class="leading-relaxed">
          Dana senilai <strong>{{ formatRupiah(booking.latest_refund?.refund_amount) }}</strong> telah berhasil ditransfer kembali ke rekening Anda pada {{ booking.latest_refund?.processed_at }}.
        </p>
      </div>

      <!-- Case 8: CANCELLED -->
      <div v-if="booking.status === 'CANCELLED'" class="p-5 bg-rose-50 rounded-2xl border border-rose-200 text-xs text-rose-950 space-y-1">
        <div class="flex items-center gap-2 font-bold text-sm text-danger">
          <span>🚫</span>
          <span>Pesanan Telah Dibatalkan</span>
        </div>
        <p class="leading-relaxed">
          Pesanan ini telah dibatalkan dan unit kapasitas telah dilepas kembali ke katalog publik.
        </p>
      </div>

      <!-- Main Booking Snapshot Details -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details Column (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
            <h3 class="text-base font-bold text-text-main border-b border-border pb-3">Rincian Produk & Pelayanan</h3>

            <div class="space-y-3 text-xs">
              <div>
                <span class="text-text-muted block font-semibold mb-0.5">Nama Produk:</span>
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

              <div class="pt-2 border-t border-border">
                <span class="text-text-muted block font-semibold mb-0.5">Mitra Operator Terverifikasi:</span>
                <span class="text-text-main font-bold">{{ booking.partner.name }}</span> ({{ booking.partner.city }})
              </div>

              <div v-if="booking.product_snapshot.tour_details?.includes_ferry" class="p-3 bg-secondary/10 rounded-xl text-secondary font-bold text-xs flex items-center gap-2">
                <span>🚢</span>
                <span>Paket ini sudah mencakup tiket resmi penyeberangan ferry Danau Toba.</span>
              </div>
            </div>
          </div>

          <!-- History Timeline -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
            <h3 class="text-base font-bold text-text-main border-b border-border pb-3">Riwayat Status Pesanan</h3>

            <div class="space-y-3 text-xs">
              <div v-for="h in booking.histories" :key="h.id" class="p-3 bg-surface-muted rounded-xl border border-border space-y-1">
                <div class="flex justify-between items-center">
                  <span class="font-bold text-primary">{{ h.actor_name }} ({{ h.actor_type }})</span>
                  <span class="text-[10px] text-text-muted">{{ h.created_at }}</span>
                </div>
                <div class="text-[11px] font-bold text-text-main">
                  Status: {{ h.previous_status }} → {{ h.new_status }}
                </div>
                <p class="text-text-muted text-[11px]">{{ h.reason }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Breakdown Sidebar (1 Col) -->
        <div class="space-y-6">
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-xl space-y-4">
            <h3 class="text-base font-bold text-text-main border-b border-border pb-3">Rincian Pembayaran</h3>

            <div class="space-y-2.5 text-xs">
              <div class="flex justify-between">
                <span class="text-text-muted">Tarif Satuan:</span>
                <span class="font-medium text-text-main">{{ formatRupiah(booking.pricing_breakdown.base_price) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-text-muted">Kuantitas:</span>
                <span class="font-medium text-text-main">
                  {{ booking.pricing_breakdown.quantity }} {{ booking.pricing_breakdown.quantity_unit }}
                </span>
              </div>
              <div class="flex justify-between font-bold pt-2 border-t border-border">
                <span>Total Biaya:</span>
                <span class="text-primary text-base font-black">{{ formatRupiah(booking.total_price) }}</span>
              </div>
              <div class="flex justify-between text-emerald-700 font-bold pt-1">
                <span>Wajib DP ({{ booking.pricing_breakdown.dp_percentage }}%):</span>
                <span>{{ formatRupiah(booking.dp_amount) }}</span>
              </div>
              <div class="flex justify-between text-text-muted text-[11px]">
                <span>Sisa Pelunasan:</span>
                <span>{{ formatRupiah(booking.balance_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Cancellation Policy Card -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider border-b border-border pb-2">Kebijakan Pembatalan</h3>
            
            <div class="space-y-2 text-xs">
              <div class="flex items-center justify-between text-emerald-800 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
                <span class="font-bold">&gt; 7 Hari Sebelum Berangkat</span>
                <span class="font-black">Refund 100%</span>
              </div>
              <div class="flex items-center justify-between text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                <span class="font-bold">3 - 7 Hari Sebelum Berangkat</span>
                <span class="font-black">Refund 50%</span>
              </div>
              <div class="flex items-center justify-between text-slate-700 bg-slate-100 p-2.5 rounded-xl border border-slate-200">
                <span class="font-bold">&lt; 3 Hari Sebelum Berangkat</span>
                <span class="font-black">Non-refundable (0%)</span>
              </div>
              <p class="text-[11px] text-text-muted pt-1 leading-relaxed">
                *Refund dihitung secara ketat dari nominal pembayaran yang telah diverifikasi (DP), bukan dari total harga paket.
              </p>
            </div>

            <!-- Cancel Button if cancellable -->
            <div v-if="booking.is_cancellable" class="pt-2 border-t border-border">
              <button
                type="button"
                @click="isCancelModalOpen = true"
                class="w-full py-2.5 px-4 rounded-xl border border-rose-300 text-danger hover:bg-rose-50 font-bold text-xs transition flex items-center justify-center gap-1.5"
              >
                <span>🚫</span>
                <span>Batalkan Pesanan Ini</span>
              </button>
            </div>
          </div>

          <!-- Need help card -->
          <div class="p-5 rounded-2xl border border-border bg-surface text-xs space-y-2 text-center">
            <strong class="text-text-main block">Butuh Bantuan Perjalanan?</strong>
            <p class="text-text-muted leading-relaxed">
              Hubungi layanan konsultasi Muter-Muter Danau Toba untuk pertanyaan ketersediaan atau kustomisasi rute.
            </p>
            <a
              href="https://wa.me/6281234567890"
              target="_blank"
              class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 hover:bg-emerald-100 transition"
            >
              <span>💬</span>
              <span>Hubungi CS via WhatsApp</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Payment Proof Modal -->
    <div
      v-if="isUploadModalOpen"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <h3 class="text-base font-bold text-text-main">Upload Bukti Transfer DP</h3>
          <button @click="isUploadModalOpen = false" class="text-text-muted hover:text-text-main font-bold">✕</button>
        </div>

        <form @submit.prevent="submitProof" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-text-muted uppercase mb-1">Nama Bank Pengirim *</label>
            <input
              v-model="proofForm.bank_sender_name"
              type="text"
              placeholder="BCA / Mandiri / BRI / BNI"
              class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
              required
            />
          </div>

          <div>
            <label class="block font-bold text-text-muted uppercase mb-1">Nomor Rekening / Nama Pemilik Rekening *</label>
            <input
              v-model="proofForm.bank_sender_account"
              type="text"
              placeholder="Contoh: 1234567890 a/n Budi Santoso"
              class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
              required
            />
          </div>

          <div>
            <label class="block font-bold text-text-muted uppercase mb-1">File Foto Struk / Screenshot Transfer (JPG/PNG/PDF, Max 5MB) *</label>
            <input
              type="file"
              @change="(e) => proofForm.proof_file = e.target.files[0]"
              accept="image/*,application/pdf"
              class="w-full text-xs text-text-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer"
              required
            />
            <span v-if="proofForm.errors.proof_file" class="text-danger mt-1 block">{{ proofForm.errors.proof_file }}</span>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-border">
            <button
              type="button"
              @click="isUploadModalOpen = false"
              class="px-4 py-2.5 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="proofForm.processing"
              class="px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-dark transition disabled:opacity-50"
            >
              {{ proofForm.processing ? 'Mengunggah...' : 'Kirim Bukti Transfer' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Cancellation Modal with Real-time Refund Calculator -->
    <div
      v-if="isCancelModalOpen"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <div class="flex items-center gap-2">
            <span class="text-danger font-bold text-base">🚫</span>
            <h3 class="text-base font-bold text-text-main">Batalkan Pesanan ({{ booking.booking_code }})</h3>
          </div>
          <button @click="isCancelModalOpen = false" class="text-text-muted hover:text-text-main font-bold">✕</button>
        </div>

        <!-- Real-Time Refund Calculator Box -->
        <div class="p-4 bg-surface-muted rounded-xl border border-border space-y-3 text-xs">
          <div class="font-bold text-text-main text-xs uppercase tracking-wider">Kalkulasi Pengembalian Dana (Refund)</div>
          
          <div class="grid grid-cols-2 gap-2 text-text-muted">
            <div>Waktu Menuju Keberangkatan:</div>
            <div class="text-right font-bold text-text-main">
              {{ Math.max(0, Math.floor(booking.hours_until_departure / 24)) }} Hari ({{ Math.max(0, booking.hours_until_departure) }} Jam)
            </div>

            <div>Total Dana Terbayar:</div>
            <div class="text-right font-bold text-text-main">
              {{ formatRupiah(booking.total_paid) }}
            </div>

            <div>Kebijakan Berlaku:</div>
            <div class="text-right font-bold" :class="booking.estimated_refund_percentage > 0 ? 'text-emerald-700' : 'text-danger'">
              Refund {{ booking.estimated_refund_percentage }}%
            </div>
          </div>

          <div class="pt-2 border-t border-border flex justify-between items-center text-sm font-bold">
            <span class="text-text-main">Estimasi Dana Dikembalikan:</span>
            <span class="text-primary font-black text-base">{{ formatRupiah(booking.estimated_refund_amount) }}</span>
          </div>
        </div>

        <form @submit.prevent="submitCancel" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-text-muted uppercase mb-1">Alasan Pembatalan *</label>
            <textarea
              v-model="cancelForm.reason"
              rows="3"
              placeholder="Jelaskan alasan Anda membatalkan pesanan ini..."
              class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
              required
            ></textarea>
            <span v-if="cancelForm.errors.reason" class="text-danger mt-1 block">{{ cancelForm.errors.reason }}</span>
          </div>

          <!-- Bank Account Info for Refund Transfer (if refund > 0) -->
          <div v-if="booking.estimated_refund_amount > 0" class="space-y-3 pt-2 border-t border-border">
            <div class="font-bold text-text-main text-xs">Rekening Tujuan Transfer Pengembalian Dana</div>

            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Nama Bank *</label>
              <input
                v-model="cancelForm.bank_name"
                type="text"
                placeholder="BCA / Mandiri / BRI / BNI"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
            </div>

            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Nomor Rekening Bank *</label>
              <input
                v-model="cancelForm.bank_account_number"
                type="text"
                placeholder="Contoh: 1234567890"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
            </div>

            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Nama Pemilik Rekening *</label>
              <input
                v-model="cancelForm.bank_account_holder"
                type="text"
                placeholder="Harus sesuai nama di buku tabungan"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
            </div>
          </div>

          <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-[11px] text-danger">
            ⚠️ <strong>Perhatian:</strong> Tindakan pembatalan ini bersifat permanen. Ketersediaan armada atau kuota tur akan segera dilepaskan kembali ke katalog publik.
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-border">
            <button
              type="button"
              @click="isCancelModalOpen = false"
              class="px-4 py-2.5 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted"
            >
              Kembali
            </button>
            <button
              type="submit"
              :disabled="cancelForm.processing"
              class="px-6 py-2.5 rounded-xl bg-danger text-white text-xs font-bold hover:bg-rose-700 transition disabled:opacity-50"
            >
              {{ cancelForm.processing ? 'Memproses...' : 'Ya, Batalkan Pesanan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
