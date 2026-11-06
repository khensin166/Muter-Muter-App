<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';

const props = defineProps({
  partner: Object,
  bookings: Object,
  counts: Object,
  filters: Object,
});

const statusTabs = [
  { key: 'PENDING_APPROVAL', label: 'Perlu Konfirmasi', count: props.counts.pending_approval, color: 'text-amber-700 bg-amber-50' },
  { key: 'AWAITING_PAYMENT', label: 'Menunggu DP', count: props.counts.awaiting_payment, color: 'text-blue-700 bg-blue-50' },
  { key: 'CONFIRMED', label: 'Terkonfirmasi', count: props.counts.confirmed, color: 'text-emerald-700 bg-emerald-50' },
  { key: 'ALL', label: 'Semua Pesanan', count: props.counts.all, color: '' },
];

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};

const approve = (id, code) => {
  if (confirm(`Setujui ketersediaan pesanan ${code}? Sistem akan menerbitkan tagihan DP dan hold inventory 24 jam.`)) {
    router.post(route('partner.bookings.approve', id));
  }
};
</script>

<template>
  <PartnerLayout>
    <Head title="Permintaan Booking — Mitra Vendor" />

    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">Kotak Masuk Operasional</span>
          <h1 class="text-2xl font-black text-text-main">Permintaan Booking & SLA 24 Jam</h1>
          <p class="text-xs text-text-muted mt-0.5">Konfirmasi ketersediaan armada atau pemandu wisata untuk mengizinkan customer membayar DP.</p>
        </div>

        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 font-semibold flex items-center gap-2">
          <span>⚡</span>
          <span>Batas Waktu Konfirmasi: Maksimal 24 Jam</span>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
        <Link
          v-for="tab in statusTabs"
          :key="tab.key"
          :href="route('partner.bookings.index', { status: tab.key })"
          :class="[
            'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0',
            filters.status === tab.key
              ? 'bg-primary text-white shadow-xs'
              : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
          ]"
        >
          <span>{{ tab.label }}</span>
          <span
            v-if="tab.count !== null"
            :class="[
              'px-2 py-0.5 rounded-full text-[10px] font-black',
              filters.status === tab.key ? 'bg-white/20 text-white' : tab.color
            ]"
          >
            {{ tab.count }}
          </span>
        </Link>
      </div>

      <!-- Bookings Table -->
      <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
        <div class="p-6 border-b border-border flex items-center justify-between">
          <h2 class="text-base font-bold text-text-main">
            Daftar Pesanan ({{ bookings.total }})
          </h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-border bg-surface-muted/50 text-[11px] font-bold text-text-muted uppercase tracking-wider">
                <th class="py-3 px-6">Kode & Pelanggan</th>
                <th class="py-3 px-6">Produk / Layanan</th>
                <th class="py-3 px-6">Tgl Keberangkatan</th>
                <th class="py-3 px-6">Status SLA</th>
                <th class="py-3 px-6">Total Biaya</th>
                <th class="py-3 px-6 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="b in bookings.data" :key="b.id" class="hover:bg-surface-muted/30 transition">
                <td class="py-4 px-6">
                  <span class="font-mono font-bold text-primary block">{{ b.booking_code }}</span>
                  <span class="font-bold text-text-main">{{ b.customer_name }}</span>
                  <span class="text-text-muted text-[11px] block">{{ b.customer_phone }}</span>
                </td>

                <td class="py-4 px-6">
                  <span class="font-bold text-text-main block">{{ b.product_name }}</span>
                  <span class="text-[11px] text-text-muted uppercase">{{ b.product_type }}</span>
                </td>

                <td class="py-4 px-6">
                  <span class="font-medium text-text-main block">{{ b.service_start_date }}</span>
                  <span class="text-[11px] text-text-muted">Jam {{ b.service_start_time || '08:00' }}</span>
                </td>

                <td class="py-4 px-6">
                  <div v-if="b.booking_status === 'PENDING_APPROVAL'">
                    <span
                      v-if="!b.is_sla_expired"
                      :class="{
                        'bg-emerald-50 text-emerald-700 border-emerald-200': b.remaining_sla_hours > 12,
                        'bg-amber-50 text-amber-700 border-amber-200': b.remaining_sla_hours <= 12 && b.remaining_sla_hours > 4,
                        'bg-rose-50 text-danger border-rose-200': b.remaining_sla_hours <= 4,
                      }"
                      class="px-2.5 py-1 rounded-full text-[10px] font-bold border block w-fit"
                    >
                      ⏱️ Sisa {{ b.remaining_sla_hours }} Jam
                    </span>
                    <span v-else class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-danger border border-rose-300 block w-fit">
                      🚨 SLA Habis
                    </span>
                  </div>
                  <span v-else class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-surface-muted text-text-muted">
                    {{ b.booking_status_label }}
                  </span>
                </td>

                <td class="py-4 px-6">
                  <span class="font-black text-primary block">{{ formatRupiah(b.total_price) }}</span>
                  <span class="text-[10px] text-text-muted">DP: {{ formatRupiah(b.dp_amount) }}</span>
                </td>

                <td class="py-4 px-6 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      v-if="b.booking_status === 'PENDING_APPROVAL'"
                      @click="approve(b.id, b.booking_code)"
                      class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs"
                    >
                      ✓ Setujui
                    </button>
                    <Link
                      :href="route('partner.bookings.show', b.id)"
                      class="px-3 py-1.5 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted transition"
                    >
                      Detail →
                    </Link>
                  </div>
                </td>
              </tr>

              <tr v-if="bookings.data.length === 0">
                <td colspan="6" class="text-center py-12 text-text-muted">
                  Tidak ada pesanan dalam status ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </PartnerLayout>
</template>

