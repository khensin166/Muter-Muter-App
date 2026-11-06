<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';

const props = defineProps({
  partner: Object,
  metrics: Object,
  incomingBookings: Array,
});
</script>

<template>
  <PartnerLayout>
    <Head :title="`Portal Mitra — ${partner.brand_name || partner.business_name}`" />

    <!-- Status Alert Banners -->
    <div
      v-if="partner.status === 'PENDING'"
      class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-8 flex items-start gap-3"
    >
      <span class="text-2xl">⏳</span>
      <div>
        <h2 class="text-sm font-bold text-amber-900">Pendaftaran Anda Sedang Dalam Peninjauan Administrator</h2>
        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
          Dokumen KTP, NIB, dan foto unit Anda sedang diverifikasi oleh tim kepatuhan Muter-Muter Danau Toba. Fitur publikasi produk dan penerimaan pesanan baru akan aktif setelah status menjadi <strong>TERVERIFIKASI (APPROVED)</strong>.
        </p>
      </div>
    </div>

    <div
      v-else-if="partner.status === 'REJECTED'"
      class="bg-rose-50 border border-rose-200 rounded-2xl p-5 mb-8 flex items-start gap-3"
    >
      <span class="text-2xl">❌</span>
      <div>
        <h2 class="text-sm font-bold text-danger">Pendaftaran Mitra Ditolak</h2>
        <p class="text-xs text-rose-800 mt-1 leading-relaxed">
          Mohon periksa kelengkapan dan kejelasan dokumen legalitas Anda. Hubungi administrator via WhatsApp untuk bantuan banding.
        </p>
      </div>
    </div>

    <!-- Partner Banner Header -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span
            :class="[
              'text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded',
              partner.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
            ]"
          >
            {{ partner.status }}
          </span>
          <span class="text-xs text-text-muted">• {{ partner.base }}</span>
        </div>
        <h1 class="text-2xl font-black text-text-main mt-1">
          {{ partner.brand_name || partner.business_name }}
        </h1>
        <p class="text-xs text-text-muted mt-1">
          PIC: <strong class="text-text-main">{{ partner.pic_name }}</strong> 
          • Role Anda: <span class="font-bold text-primary">{{ partner.role }}</span>
          • Bank Payout: <span class="font-medium text-text-main">{{ partner.bank_name }} ({{ partner.bank_account_number }})</span>
        </p>
      </div>

      <div class="flex items-center gap-3">
        <Link
          v-if="partner.role === 'OWNER'"
          :href="route('partner.staff.index')"
          class="bg-surface border border-border hover:bg-surface-muted text-text-main text-xs font-bold py-2.5 px-4 rounded-xl transition"
        >
          👥 Kelola Staf Tim
        </Link>
      </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Permintaan Baru</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-accent">{{ metrics.pendingCount }}</span>
          <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">SLA 24 Jam</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Membutuhkan konfirmasi ketersediaan</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Produk / Armada Aktif</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-primary">{{ metrics.productsCount }}</span>
          <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Katalog</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Paket tour & unit rental terdaftar</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Trip Terjadwal</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-secondary">{{ metrics.confirmedCount }}</span>
          <span class="text-xs font-semibold text-secondary bg-secondary-light px-2 py-0.5 rounded">Terkonfirmasi</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Jadwal operasional aktif</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Siap Dicairkan</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-text-main">{{ metrics.readySettlement }}</span>
          <span class="text-xs font-bold text-text-muted">Net</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Pasca-trip selesai & potongan komisi 10%</span>
      </div>
    </div>

    <!-- SLA Incoming Request Table -->
    <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden mb-8">
      <div class="p-6 border-b border-border flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-text-main">Antrean Permintaan Booking (SLA Konfirmasi 24 Jam)</h2>
          <p class="text-xs text-text-muted mt-0.5">
            Konfirmasi ketersediaan armada atau guide agar customer dapat membayar DP dalam masa hold 24 jam.
          </p>
        </div>
        <span class="text-xs font-bold text-primary bg-primary/10 px-3 py-1 rounded-full">
          {{ incomingBookings.length }} Perlu Respon
        </span>
      </div>

      <div v-if="incomingBookings.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-background text-text-muted uppercase tracking-wider text-[11px] border-b border-border">
            <tr>
              <th class="p-4 pl-6">Kode & Pemesan</th>
              <th class="p-4">Paket / Armada</th>
              <th class="p-4">Jadwal Keberangkatan</th>
              <th class="p-4">Total & DP (30%)</th>
              <th class="p-4">Sisa Waktu SLA</th>
              <th class="p-4 pr-6 text-right">Aksi Konfirmasi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="b in incomingBookings" :key="b.code" class="hover:bg-surface-muted/50 transition">
              <td class="p-4 pl-6">
                <span class="font-bold text-primary block">{{ b.code }}</span>
                <span class="text-text-main font-semibold">{{ b.customer }}</span>
                <span class="text-text-muted block text-[11px]">{{ b.phone }}</span>
              </td>
              <td class="p-4">
                <span class="text-xs font-bold uppercase px-2 py-0.5 rounded bg-primary/10 text-primary inline-block mb-1">
                  {{ b.type }}
                </span>
                <div class="font-semibold text-text-main">{{ b.product }}</div>
              </td>
              <td class="p-4 font-medium text-text-main">
                {{ b.dates }}
              </td>
              <td class="p-4">
                <div class="font-bold text-text-main">{{ b.totalPrice }}</div>
                <div class="text-[11px] text-accent font-semibold">DP: {{ b.dpTarget }}</div>
              </td>
              <td class="p-4">
                <div
                  :class="[
                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-bold text-xs',
                    b.slaStatus === 'URGENT' ? 'bg-rose-100 text-danger border border-rose-200 animate-pulse' : 'bg-amber-50 text-amber-800 border border-amber-200'
                  ]"
                >
                  <span>⏱️</span>
                  <span>{{ b.slaRemaining }}</span>
                </div>
              </td>
              <td class="p-4 pr-6 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-xs transition">
                    ✓ Setujui
                  </button>
                  <button class="border border-border text-text-muted hover:text-danger hover:border-danger font-semibold py-1.5 px-2.5 rounded-lg transition">
                    ✕ Tolak
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="p-10 text-center text-text-muted text-xs">
        <span>🎉 Belum ada permintaan booking baru yang menunggu konfirmasi saat ini.</span>
      </div>
    </div>
  </PartnerLayout>
</template>

