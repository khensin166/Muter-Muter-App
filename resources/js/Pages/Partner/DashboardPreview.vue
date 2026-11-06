<script setup>
import { Head } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';

const partnerInfo = {
  name: 'Horas Samosir Tour & Transport',
  owner: 'Togu Simanjuntak',
  role: 'Partner Owner',
  status: 'TERVERIFIKASI',
  base: 'Tomok, Samosir',
};

const incomingBookings = [
  {
    code: 'BK-202611-0042',
    customer: 'Budi Santoso (Jakarta)',
    phone: '0812-9876-xxxx',
    product: '3D2N Eksotik Samosir & Danau Toba',
    type: 'TOUR',
    dates: '12 Nov 2026 - 14 Nov 2026',
    guests: '4 Peserta (Private)',
    totalPrice: 'Rp 5.800.000',
    dpTarget: 'Rp 1.740.000 (30%)',
    slaRemaining: '06:45:12', // 6 hours remaining
    slaStatus: 'URGENT',
  },
  {
    code: 'BK-202611-0043',
    customer: 'Siti Rahma (Medan)',
    phone: '0813-1122-xxxx',
    product: 'Toyota HiAce Commuter (14 Seat) + Driver',
    type: 'VEHICLE',
    dates: '15 Nov 2026 (1 Hari)',
    guests: '12 Penumpang',
    totalPrice: 'Rp 1.350.000',
    dpTarget: 'Rp 405.000 (30%)',
    slaRemaining: '18:20:00', // 18 hours remaining
    slaStatus: 'NORMAL',
  },
];
</script>

<template>
  <PartnerLayout>
    <Head title="Portal Mitra — Ringkasan & SLA" />

    <!-- Partner Banner -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
            {{ partnerInfo.status }}
          </span>
          <span class="text-xs text-text-muted">• {{ partnerInfo.base }}</span>
        </div>
        <h1 class="text-2xl font-black text-text-main mt-1">{{ partnerInfo.name }}</h1>
        <p class="text-xs text-text-muted mt-1">
          PIC: <strong class="text-text-main">{{ partnerInfo.owner }}</strong> (Hak Akses: {{ partnerInfo.role }})
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button class="bg-primary hover:bg-primary-dark text-white text-xs font-bold py-2.5 px-4 rounded-xl transition">
          + Tambah Armada / Paket
        </button>
      </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Permintaan Baru</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-accent">2</span>
          <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">SLA 24h</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Membutuhkan konfirmasi ketersediaan</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Trip Terjadwal Minggu Ini</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-primary">5</span>
          <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Armada Siap</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">1 Open Trip, 4 Private Tour</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Siap Dicairkan (Settlement)</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-text-main">Rp 8.450.000</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Trip selesai & terverifikasi admin</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Tingkat Respon SLA</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-secondary">98.2%</span>
          <span class="text-xs font-bold text-secondary bg-secondary-light px-2 py-0.5 rounded">Sangat Cepat</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Rata-rata respon: 1.4 jam</span>
      </div>
    </div>

    <!-- SLA Incoming Request Table -->
    <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden mb-8">
      <div class="p-6 border-b border-border flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-text-main">Antrean Permintaan Booking (SLA Konfirmasi 24 Jam)</h2>
          <p class="text-xs text-text-muted mt-0.5">
            Segera setujui ketersediaan agar customer dapat membayar DP dan sistem menahan kuota (Hold 24 Jam).
          </p>
        </div>
        <span class="text-xs font-bold text-amber-700 bg-amber-100 px-3 py-1 rounded-full">
          2 Perlu Respon
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-background text-text-muted uppercase tracking-wider text-[11px] border-b border-border">
            <tr>
              <th class="p-4 pl-6">Kode Booking & Pemesan</th>
              <th class="p-4">Paket / Kendaraan</th>
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
                <div class="text-[11px] text-text-muted">{{ b.guests }}</div>
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
                <span v-if="b.slaStatus === 'URGENT'" class="block text-[10px] text-danger font-medium mt-0.5">Segera konfirmasi!</span>
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
    </div>
  </PartnerLayout>
</template>

