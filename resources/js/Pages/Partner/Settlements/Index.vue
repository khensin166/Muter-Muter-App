<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';

const props = defineProps({
  settlements: Object,
  totals: Object,
  filters: Object,
  partnerBank: Object,
});


const statusTabs = [
  { key: 'ALL', label: 'Semua Transaksi' },
  { key: 'ELIGIBLE', label: 'Siap Dicairkan' },
  { key: 'PENDING', label: 'Menunggu Trip Selesai' },
  { key: 'PAID', label: 'Sudah Dicairkan' },
  { key: 'ON_HOLD', label: 'Ditahan / Dispute' },
];
</script>

<template>
  <PartnerLayout>
    <Head title="Buku Kas & Pencairan Dana (Settlement) — Mitra" />

    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">Financial & Settlement Ledger</span>
          <h1 class="text-2xl font-black text-text-main">Pencairan Dana Mitra</h1>
          <p class="text-xs text-text-muted mt-0.5">
            Kelola pendapatan bersih setelah dipotong komisi platform 10%. Dana otomatis siap dicairkan setelah status perjalanan SELESAI.
          </p>
        </div>

        <!-- Bank Account Badge -->
        <div class="p-3 bg-surface rounded-xl border border-border shadow-xs text-xs space-y-0.5">
          <span class="text-[10px] text-text-muted uppercase font-bold block">Rekening Penampung Pencairan:</span>
          <div class="font-bold text-text-main">
            {{ partnerBank.bank_name || 'Belum diatur' }} — {{ partnerBank.bank_account_number || '-' }}
          </div>
          <div class="text-[11px] text-text-muted">a/n {{ partnerBank.bank_account_holder || '-' }}</div>
        </div>
      </div>

      <!-- Financial Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
          <span class="text-xs text-text-muted font-bold block">Total Sudah Dicairkan</span>
          <div class="text-2xl font-black text-emerald-700">{{ formatRupiah(totals.paid) }}</div>
          <span class="text-[10px] text-text-muted block">Telah ditransfer ke rekening mitra</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-emerald-300 shadow-xs space-y-1 bg-emerald-50/50">
          <span class="text-xs text-emerald-900 font-bold block">Siap Dicairkan (Eligible)</span>
          <div class="text-2xl font-black text-primary">{{ formatRupiah(totals.eligible) }}</div>
          <span class="text-[10px] text-emerald-800 block">Jadwal trip selesai, menunggu jadwal transfer admin</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
          <span class="text-xs text-text-muted font-bold block">Menunggu Trip Selesai</span>
          <div class="text-2xl font-black text-slate-800">{{ formatRupiah(totals.pending) }}</div>
          <span class="text-[10px] text-text-muted block">Booking aktif & jadwal belum terlewati</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
          <span class="text-xs text-text-muted font-bold block">Ditahan (Dispute / Batal)</span>
          <div class="text-2xl font-black text-danger">{{ formatRupiah(totals.on_hold) }}</div>
          <span class="text-[10px] text-text-muted block">Pesanan dibatalkan / refund</span>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <Link
          v-for="tab in statusTabs"
          :key="tab.key"
          :href="route('partner.settlements.index', { status: tab.key })"
          :class="[
            'px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition border',
            filters.status === tab.key
              ? 'bg-primary text-white border-primary shadow-xs'
              : 'bg-surface text-text-muted hover:text-text-main border-border'
          ]"
        >
          {{ tab.label }}
        </Link>
      </div>

      <!-- Ledger Table -->
      <div class="bg-surface rounded-2xl border border-border overflow-hidden shadow-card">
        <div v-if="settlements.data.length === 0" class="p-12 text-center space-y-2">
          <div class="text-3xl">📊</div>
          <div class="font-bold text-text-main text-sm">Belum ada catatan transaksi settlement</div>
          <p class="text-xs text-text-muted">Pencairan akan otomatis tercatat ketika ada pesanan booking yang telah terkonfirmasi.</p>
        </div>

        <table v-else class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-border bg-surface-muted/50 text-text-muted uppercase text-[10px] font-bold tracking-wider">
              <th class="py-3.5 px-4">Kode Booking</th>
              <th class="py-3.5 px-4">Produk & Pelanggan</th>
              <th class="py-3.5 px-4">Tgl Keberangkatan</th>
              <th class="py-3.5 px-4">Nilai Bruto</th>
              <th class="py-3.5 px-4">Komisi Platform (10%)</th>
              <th class="py-3.5 px-4">Pendapatan Bersih Mitra</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4">Ref Transfer</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="s in settlements.data" :key="s.id" class="hover:bg-surface-muted/30 transition">
              <td class="py-3 px-4 font-mono font-bold text-text-main">
                <Link :href="route('partner.bookings.show', s.id)" class="hover:text-primary underline">
                  {{ s.booking_code }}
                </Link>
                <div class="text-[10px] font-sans text-text-muted">{{ s.created_at }}</div>
              </td>
              <td class="py-3 px-4">
                <div class="font-bold text-text-main">{{ s.product_name }}</div>
                <div class="text-[10px] text-text-muted">Pelanggan: {{ s.customer_name }}</div>
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
                <div class="font-black text-emerald-700 text-sm">
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
              <td class="py-3 px-4 text-[11px] font-mono text-text-muted">
                <span v-if="s.bank_reference" class="font-bold text-slate-800">{{ s.bank_reference }}</span>
                <span v-else class="italic text-text-muted">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </PartnerLayout>
</template>

