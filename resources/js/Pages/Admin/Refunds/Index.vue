<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  refunds: Object,
  counts: Object,
  filters: Object,
});

const statusTabs = [
  { key: 'PENDING', label: 'Menunggu Pengembalian', count: props.counts.pending, color: 'text-amber-700 bg-amber-50' },
  { key: 'PROCESSED', label: 'Selesai Ditransfer', count: props.counts.processed, color: 'text-emerald-700 bg-emerald-50' },
  { key: 'REJECTED', label: 'Ditolak', count: props.counts.rejected, color: 'text-danger bg-rose-50' },
  { key: 'ALL', label: 'Semua Refund', count: props.counts.all, color: '' },
];

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
    <Head title="Kelola Pengembalian Dana (Refund) — Admin Console" />

    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-text-main">Antrean Pengembalian Dana (Refund)</h1>
        <p class="text-xs text-text-muted mt-0.5">
          Proses transfer refund ke rekening bank pelanggan sesuai snapshot kebijakan pembatalan Danau Toba Travel Marketplace.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200">
          Refund SLA 48 Jam
        </span>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
      <Link
        v-for="tab in statusTabs"
        :key="tab.key"
        :href="route('admin.refunds.index', { status: tab.key })"
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

    <!-- Refunds Table -->
    <div class="bg-surface rounded-2xl border border-border overflow-hidden shadow-card">
      <div v-if="refunds.data.length === 0" class="p-12 text-center space-y-2">
        <div class="text-3xl">💸</div>
        <div class="font-bold text-text-main text-sm">Tidak ada antrean refund</div>
        <p class="text-xs text-text-muted">Semua pengajuan pengembalian dana telah diproses atau tidak ditemukan transaksi.</p>
      </div>

      <table v-else class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-border bg-surface-muted/50 text-text-muted uppercase text-[10px] font-bold tracking-wider">
            <th class="py-3.5 px-4">Kode Booking</th>
            <th class="py-3.5 px-4">Pelanggan</th>
            <th class="py-3.5 px-4">Mitra Vendor</th>
            <th class="py-3.5 px-4">Rekening Tujuan</th>
            <th class="py-3.5 px-4">Nominal Refund</th>
            <th class="py-3.5 px-4">Status</th>
            <th class="py-3.5 px-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr v-for="r in refunds.data" :key="r.id" class="hover:bg-surface-muted/30 transition">
            <td class="py-3 px-4 font-mono font-bold text-text-main">
              <Link :href="route('admin.refunds.show', r.id)" class="hover:text-primary underline">
                {{ r.booking_code }}
              </Link>
              <div class="text-[10px] font-sans text-text-muted">{{ r.created_at }}</div>
            </td>
            <td class="py-3 px-4">
              <div class="font-bold text-text-main">{{ r.customer_name }}</div>
              <div class="text-[10px] text-text-muted">{{ r.customer_phone }}</div>
            </td>
            <td class="py-3 px-4 text-text-main font-medium">
              {{ r.partner_name || 'Internal Platform' }}
            </td>
            <td class="py-3 px-4">
              <div class="font-bold text-slate-800">{{ r.bank_name }} - {{ r.bank_account_number }}</div>
              <div class="text-[10px] text-text-muted">a/n {{ r.bank_account_holder }}</div>
            </td>
            <td class="py-3 px-4">
              <div class="font-black text-primary text-sm">{{ formatRupiah(r.refund_amount) }}</div>
              <div class="text-[10px] text-text-muted">Persentase: {{ r.calculated_refund_percentage }}%</div>
            </td>
            <td class="py-3 px-4">
              <span
                :class="{
                  'bg-amber-100 text-amber-900 border-amber-300': r.status === 'PENDING',
                  'bg-emerald-100 text-emerald-900 border-emerald-300': r.status === 'PROCESSED',
                  'bg-rose-100 text-danger border-rose-300': r.status === 'REJECTED',
                }"
                class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider"
              >
                {{ r.status }}
              </span>
            </td>
            <td class="py-3 px-4 text-right">
              <Link
                :href="route('admin.refunds.show', r.id)"
                class="px-3 py-1.5 rounded-xl bg-primary text-white text-[11px] font-bold hover:bg-primary-dark transition shadow-2xs"
              >
                Periksa & Transfer →
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

