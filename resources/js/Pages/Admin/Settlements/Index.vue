<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  settlements: Object,
  counts: Object,
  totals: Object,
  filters: Object,
});

const activeModalSettlement = ref(null);

const payoutForm = useForm({
  bank_reference: '',
});

const openPayoutModal = (settlement) => {
  activeModalSettlement.value = settlement;
  payoutForm.reset();
};

const closePayoutModal = () => {
  activeModalSettlement.value = null;
  payoutForm.reset();
};

const submitPayout = () => {
  if (!activeModalSettlement.value) return;

  payoutForm.post(route('admin.settlements.payout', activeModalSettlement.value.id), {
    onSuccess: () => closePayoutModal(),
  });
};


const statusTabs = [
  { key: 'ELIGIBLE', label: 'Siap Dicairkan', count: props.counts.eligible, color: 'text-primary bg-primary/10' },
  { key: 'PENDING', label: 'Menunggu Trip Selesai', count: props.counts.pending, color: 'text-slate-700 bg-slate-100' },
  { key: 'PAID', label: 'Sudah Dicairkan', count: props.counts.paid, color: 'text-emerald-700 bg-emerald-50' },
  { key: 'ON_HOLD', label: 'Ditahan', count: props.counts.on_hold, color: 'text-danger bg-rose-50' },
  { key: 'ALL', label: 'Semua Transaksi', count: props.counts.all, color: '' },
];
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Pencairan Mitra (Settlement) — Admin Console" />

    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-text-main">Manajemen Pencairan Dana Mitra (Vendor Settlement)</h1>
        <p class="text-xs text-text-muted mt-0.5">
          Proses pembayaran pendapatan bersih mitra setelah perjalanan selesai, dikurangi komisi platform 10%.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
          Platform Commission: 10%
        </span>
      </div>
    </div>

    <!-- Financial Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="p-5 bg-surface rounded-2xl border border-primary/30 shadow-xs space-y-1 bg-primary/5">
        <span class="text-xs text-primary font-bold block">Siap Dicairkan (Eligible Payout)</span>
        <div class="text-2xl font-black text-primary">{{ formatRupiah(totals.eligible_payout) }}</div>
        <span class="text-[10px] text-text-muted block">Jadwal trip selesai, menunggu transfer ke mitra</span>
      </div>

      <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
        <span class="text-xs text-text-muted font-bold block">Total Komisi Platform Terkumpul</span>
        <div class="text-2xl font-black text-emerald-700">{{ formatRupiah(totals.total_commission_collected) }}</div>
        <span class="text-[10px] text-text-muted block">Pendapatan bersih marketplace (10% potongan)</span>
      </div>

      <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
        <span class="text-xs text-text-muted font-bold block">Total Telah Ditransfer ke Mitra</span>
        <div class="text-2xl font-black text-slate-800">{{ formatRupiah(totals.total_paid_out) }}</div>
        <span class="text-[10px] text-text-muted block">Dana sukses terdistribusi ke rekening mitra</span>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
      <Link
        v-for="tab in statusTabs"
        :key="tab.key"
        :href="route('admin.settlements.index', { status: tab.key })"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition border',
          filters.status === tab.key
            ? 'bg-primary text-white border-primary shadow-xs'
            : 'bg-surface text-text-muted hover:text-text-main border-border'
        ]"
      >
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.count !== undefined"
          :class="[
            'ml-2 px-1.5 py-0.5 rounded-full text-[10px]',
            filters.status === tab.key ? 'bg-white/20 text-white' : (tab.color || 'bg-surface-muted text-text-muted')
          ]"
        >
          {{ tab.count }}
        </span>
      </Link>
    </div>

    <!-- Settlements Table -->
    <div class="bg-surface rounded-2xl border border-border overflow-hidden shadow-card">
      <div v-if="settlements.data.length === 0" class="p-12 text-center space-y-2">
        <div class="text-3xl">🏦</div>
        <div class="font-bold text-text-main text-sm">Tidak ada transaksi settlement</div>
        <p class="text-xs text-text-muted">Tidak ditemukan data pencairan dengan status yang dipilih.</p>
      </div>

      <table v-else class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-border bg-surface-muted/50 text-text-muted uppercase text-[10px] font-bold tracking-wider">
            <th class="py-3.5 px-4">Kode Booking</th>
            <th class="py-3.5 px-4">Mitra Vendor & Rekening</th>
            <th class="py-3.5 px-4">Tgl Keberangkatan</th>
            <th class="py-3.5 px-4">Nilai Transaksi</th>
            <th class="py-3.5 px-4">Komisi (10%)</th>
            <th class="py-3.5 px-4">Wajib Transfer (Net)</th>
            <th class="py-3.5 px-4">Status</th>
            <th class="py-3.5 px-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-for="s in settlements.data" :key="s.id" class="hover:bg-surface-muted/30 transition">
            <td class="py-3 px-4 font-mono font-bold text-text-main">
              <span>{{ s.booking_code }}</span>
              <div class="text-[10px] font-sans text-text-muted">{{ s.created_at }}</div>
            </td>
            <td class="py-3 px-4">
              <div class="font-bold text-text-main">{{ s.partner_name }}</div>
              <div class="text-[11px] text-slate-800 font-mono">
                {{ s.partner_bank.bank_name || '-' }} - {{ s.partner_bank.bank_account_number || '-' }}
              </div>
              <div class="text-[10px] text-text-muted">a/n {{ s.partner_bank.bank_account_holder || '-' }}</div>
            </td>
            <td class="py-3 px-4 text-text-muted font-medium">
              {{ s.service_date }}
            </td>
            <td class="py-3 px-4 font-medium text-text-main">
              {{ formatRupiah(s.gross_amount) }}
            </td>
            <td class="py-3 px-4 text-danger font-semibold">
              - {{ formatRupiah(s.commission_amount) }}
            </td>
            <td class="py-3 px-4">
              <div class="font-black text-primary text-sm">
                {{ formatRupiah(s.net_payout_amount) }}
              </div>
            </td>
            <td class="py-3 px-4">
              <span
                :class="{
                  'bg-slate-100 text-slate-800 border-slate-300': s.status === 'PENDING',
                  'bg-blue-100 text-blue-900 border-blue-300': s.status === 'ELIGIBLE',
                  'bg-amber-100 text-amber-900 border-amber-300': s.status === 'PROCESSING',
                  'bg-emerald-100 text-emerald-900 border-emerald-300': s.status === 'PAID',
                  'bg-rose-100 text-danger border-rose-300': s.status === 'ON_HOLD',
                }"
                class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider"
              >
                {{ s.status }}
              </span>
            </td>
            <td class="py-3 px-4 text-right">
              <button
                v-if="s.status === 'ELIGIBLE'"
                @click="openPayoutModal(s)"
                class="px-3 py-1.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-[11px] font-bold transition shadow-2xs"
              >
                Proses Payout →
              </button>
              <div v-else-if="s.status === 'PAID'" class="text-[10px] font-mono text-emerald-700 font-bold">
                ✓ Ref: {{ s.bank_reference }}
              </div>
              <span v-else class="text-text-muted text-[11px] italic">-</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Payout Modal -->
    <div
      v-if="activeModalSettlement"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <div class="flex items-center gap-2">
            <span class="text-xl">💸</span>
            <h3 class="text-base font-bold text-text-main">Konfirmasi Pencairan Dana Mitra</h3>
          </div>
          <button @click="closePayoutModal" class="text-text-muted hover:text-text-main font-bold">✕</button>
        </div>

        <div class="bg-surface-muted p-4 rounded-xl border border-border space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-text-muted">Mitra Penerima:</span>
            <span class="font-bold text-text-main">{{ activeModalSettlement.partner_name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-text-muted">Rekening Tujuan:</span>
            <span class="font-bold font-mono text-slate-800">
              {{ activeModalSettlement.partner_bank.bank_name }} - {{ activeModalSettlement.partner_bank.bank_account_number }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-text-muted">Atas Nama:</span>
            <span class="font-bold text-text-main">{{ activeModalSettlement.partner_bank.bank_account_holder }}</span>
          </div>
          <div class="flex justify-between items-center pt-2 border-t border-border font-bold text-sm">
            <span>Nominal Bersih Ditransfer:</span>
            <span class="text-primary font-black text-base">{{ formatRupiah(activeModalSettlement.net_payout_amount) }}</span>
          </div>
        </div>

        <form @submit.prevent="submitPayout" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-text-muted uppercase mb-1">Nomor Referensi Transfer Bank *</label>
            <input
              v-model="payoutForm.bank_reference"
              type="text"
              placeholder="Contoh: TRF-MANDIRI-202611-0091"
              class="w-full text-xs font-mono font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
              required
            />
            <span v-if="payoutForm.errors.bank_reference" class="text-danger mt-1 block">
              {{ payoutForm.errors.bank_reference }}
            </span>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-border">
            <button
              type="button"
              @click="closePayoutModal"
              class="px-4 py-2.5 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="payoutForm.processing"
              class="px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-dark transition disabled:opacity-50"
            >
              {{ payoutForm.processing ? 'Memproses...' : 'Catat Payout Selesai' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

