<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const pendingPayments = [
  {
    code: 'BK-202611-0038',
    customer: 'Reza Pratama (Bandung)',
    phone: '0811-7788-xxxx',
    product: 'One Day Trip Samosir Heritage & Tele',
    dpAmount: 'Rp 380.000 (DP 30%)',
    bankSource: 'BCA (an Reza Pratama)',
    bankDest: 'BCA Platform Rekening',
    transferredAt: '06 Nov 2026, 11:40 WIB',
    proofImage: 'bukti_transfer_reza.jpg',
  },
  {
    code: 'BK-202611-0039',
    customer: 'Dewi Lestari (Surabaya)',
    phone: '0852-3344-xxxx',
    product: 'Toyota Innova Reborn + Driver (3 Hari)',
    dpAmount: 'Rp 765.000 (DP 30%)',
    bankSource: 'Mandiri (an Dewi Lestari)',
    bankDest: 'Mandiri Platform Rekening',
    transferredAt: '06 Nov 2026, 10:15 WIB',
    proofImage: 'transfer_dewi_innova.png',
  },
];

const slaUrgentBookings = [
  {
    code: 'BK-202611-0031',
    customer: 'Andi Wijaya (Medan)',
    vendor: 'Toba Sunrise Travel',
    vendorPhone: '0812-4455-6677',
    product: '3D2N Pesona Danau Toba',
    overdueTime: 'Lewat 2 jam (Total 26 Jam tanpa respon)',
  },
];
</script>

<template>
  <AdminLayout>
    <Head title="Admin Operations Console — Lake Toba Marketplace" />

    <!-- Emergency SLA Intervensi Banner -->
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-danger flex items-center justify-center text-white font-bold text-lg shrink-0">
          ⚠️
        </div>
        <div>
          <h2 class="text-sm font-bold text-danger">Peringatan SLA Vendor: 1 Pesanan Perlu Intervensi Manual</h2>
          <p class="text-xs text-rose-800">
            Mitra tidak merespons dalam 24 jam. Segera hubungi vendor atau alihkan ke armada mitra lain agar pesanan tidak batal.
          </p>
        </div>
      </div>
      <button class="bg-danger hover:bg-rose-700 text-white text-xs font-bold py-2 px-4 rounded-xl shadow-xs transition shrink-0">
        Buka Antrean Intervensi (1)
      </button>
    </div>

    <!-- Operations Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Bukti Bayar Menunggu</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-accent">2</span>
          <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Perlu Verifikasi</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Cek mutasi bank sebelum konfirmasi</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Komisi Platform (10%)</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-2xl font-black text-primary">Rp 4.280.000</span>
          <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Bulan Ini</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Dari 28 booking terselesaikan</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Verifikasi Dokumen Mitra</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-3xl font-black text-text-main">3</span>
          <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded">KYC Baru</span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">KTP, NIB, & Foto Unit Baru</span>
      </div>

      <div class="bg-surface rounded-2xl p-5 border border-border shadow-xs">
        <span class="text-xs font-bold text-text-muted uppercase tracking-wider block">Status WhatsApp Gateway</span>
        <div class="flex items-baseline justify-between mt-2">
          <span class="text-lg font-black text-secondary">CONNECTED</span>
          <span class="w-3 h-3 rounded-full bg-secondary animate-ping"></span>
        </div>
        <span class="text-[11px] text-text-muted mt-2 block">Internal Go WA Gateway Aktif</span>
      </div>
    </div>

    <!-- Payment Proof Verification Queue -->
    <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden mb-8">
      <div class="p-6 border-b border-border flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-text-main">Verifikasi Bukti Transfer DP (Manual Bank Transfer)</h2>
          <p class="text-xs text-text-muted mt-0.5">
            Cocokkan nominal dan nama pengirim dengan mutasi rekening platform sebelum menyetujui status booking menjadi CONFIRMED.
          </p>
        </div>
        <span class="text-xs font-bold text-accent bg-accent/10 px-3 py-1 rounded-full">
          2 Menunggu Verifikasi
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-background text-text-muted uppercase tracking-wider text-[11px] border-b border-border">
            <tr>
              <th class="p-4 pl-6">Kode & Customer</th>
              <th class="p-4">Paket / Layanan</th>
              <th class="p-4">Nominal Transfer</th>
              <th class="p-4">Rekening Pengirim & Tujuan</th>
              <th class="p-4">Bukti Struk</th>
              <th class="p-4 pr-6 text-right">Verifikasi Admin</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="p in pendingPayments" :key="p.code" class="hover:bg-surface-muted/50 transition">
              <td class="p-4 pl-6">
                <span class="font-bold text-primary block">{{ p.code }}</span>
                <span class="text-text-main font-semibold">{{ p.customer }}</span>
                <span class="text-text-muted block text-[11px]">{{ p.phone }}</span>
              </td>
              <td class="p-4 font-medium text-text-main">
                {{ p.product }}
              </td>
              <td class="p-4">
                <span class="font-black text-sm text-text-main block">{{ p.dpAmount }}</span>
                <span class="text-[10px] text-text-muted">{{ p.transferredAt }}</span>
              </td>
              <td class="p-4">
                <div class="font-semibold text-text-main">{{ p.bankSource }}</div>
                <div class="text-[11px] text-text-muted">→ {{ p.bankDest }}</div>
              </td>
              <td class="p-4">
                <button class="text-xs font-semibold text-primary underline hover:text-primary-dark">
                  🖼️ Lihat Foto Struk
                </button>
              </td>
              <td class="p-4 pr-6 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-xs transition">
                    ✓ Sahkan (Confirmed)
                  </button>
                  <button class="border border-border text-danger hover:bg-rose-50 font-semibold py-1.5 px-2.5 rounded-lg transition">
                    ✕ Tolak Bukti
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

