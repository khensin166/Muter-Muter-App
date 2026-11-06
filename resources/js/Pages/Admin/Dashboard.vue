<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  metrics: Object,
  urgentPayments: Array,
  urgentSla: Array,
  recentBookings: Array,
});

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
    <Head title="Pusat Kendali Operasional — Super Admin" />

    <!-- Header -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs font-bold text-accent uppercase tracking-wider">Super Administrator Console</span>
        <h1 class="text-2xl font-black text-text-main">Pusat Kendali Operasional Danau Toba</h1>
        <p class="text-xs text-text-muted mt-0.5">
          Pantau kesehatan platform, verifikasi KYC mitra, validasi pembayaran, dan intervensi operasional SLA secara real-time.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
          Sistem Online • PostgreSQL Active
        </span>
      </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- 1. Pending Payments -->
      <Link
        :href="route('admin.payments.index')"
        class="p-5 bg-surface rounded-2xl border border-border hover:border-primary shadow-xs transition space-y-2 group"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs text-text-muted font-bold">Verifikasi Bukti DP</span>
          <span class="text-lg">💳</span>
        </div>
        <div class="text-3xl font-black text-text-main group-hover:text-primary transition">
          {{ metrics.pending_payments }}
        </div>
        <span class="text-[11px] text-amber-700 font-semibold block">Perlu pencocokan mutasi bank</span>
      </Link>

      <!-- 2. SLA Breached -->
      <Link
        :href="route('admin.sla.index')"
        class="p-5 bg-surface rounded-2xl border border-border hover:border-danger shadow-xs transition space-y-2 group"
        :class="{ 'bg-rose-50/50 border-rose-300': metrics.sla_breached > 0 }"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs text-text-muted font-bold">Intervensi Manual SLA</span>
          <span class="text-lg">🚨</span>
        </div>
        <div class="text-3xl font-black" :class="metrics.sla_breached > 0 ? 'text-danger' : 'text-text-main'">
          {{ metrics.sla_breached }}
        </div>
        <span class="text-[11px] text-danger font-semibold block">Mitra >24 jam tanpa respon</span>
      </Link>

      <!-- 3. Pending KYC -->
      <Link
        :href="route('admin.partners.index')"
        class="p-5 bg-surface rounded-2xl border border-border hover:border-primary shadow-xs transition space-y-2 group"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs text-text-muted font-bold">Verifikasi Mitra (KYC)</span>
          <span class="text-lg">🏢</span>
        </div>
        <div class="text-3xl font-black text-text-main group-hover:text-primary transition">
          {{ metrics.pending_partners }}
        </div>
        <span class="text-[11px] text-text-muted block">Menunggu verifikasi KTP & NIB</span>
      </Link>

      <!-- 4. Platform Commission -->
      <Link
        :href="route('admin.settlements.index')"
        class="p-5 bg-surface rounded-2xl border border-border hover:border-emerald-500 shadow-xs transition space-y-2 group"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs text-text-muted font-bold">Komisi Platform (10%)</span>
          <span class="text-lg">💰</span>
        </div>
        <div class="text-2xl font-black text-emerald-700">
          {{ formatRupiah(metrics.total_commission_collected) }}
        </div>
        <span class="text-[11px] text-text-muted block">Total pendapatan fee transaksi</span>
      </Link>
    </div>

    <!-- Alert Box: SLA Breaches -->
    <div v-if="urgentSla.length > 0" class="mb-8 p-5 bg-rose-50 border border-rose-200 rounded-2xl space-y-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-danger font-bold text-sm">
          <span>⚠️</span>
          <span>Perlu Intervensi Segera: Mitra Vendor Melewati SLA 24 Jam</span>
        </div>
        <Link
          :href="route('admin.sla.index')"
          class="text-xs font-bold text-danger hover:underline"
        >
          Lihat Semua ({{ metrics.sla_breached }}) →
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
        <div
          v-for="item in urgentSla"
          :key="item.id"
          class="p-3 bg-white rounded-xl border border-rose-200 flex justify-between items-center"
        >
          <div>
            <div class="font-mono font-bold text-text-main">{{ item.booking_code }} • {{ item.customer_name }}</div>
            <div class="text-text-muted text-[11px]">{{ item.product_name }}</div>
            <div class="text-danger font-semibold text-[11px] mt-0.5">
              Mitra: {{ item.partner_name }} ({{ item.partner_phone || '-' }})
            </div>
          </div>
          <Link
            :href="route('admin.sla.index')"
            class="px-3 py-1.5 bg-danger text-white rounded-lg text-[11px] font-bold hover:bg-rose-700 transition"
          >
            Intervensi
          </Link>
        </div>
      </div>
    </div>

    <!-- Two Columns: Payments & Recent Bookings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Left Column: Pending Payments -->
      <div class="bg-surface rounded-2xl border border-border p-6 shadow-card space-y-4">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <div class="flex items-center gap-2 font-bold text-base text-text-main">
            <span>📥</span>
            <span>Bukti Bayar DP Terbaru</span>
          </div>
          <Link
            :href="route('admin.payments.index')"
            class="text-xs text-primary font-bold hover:underline"
          >
            Lihat Antrean →
          </Link>
        </div>

        <div v-if="urgentPayments.length === 0" class="py-8 text-center text-text-muted text-xs">
          Belum ada bukti pembayaran yang menunggu verifikasi.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="p in urgentPayments"
            :key="p.id"
            class="p-3.5 bg-surface-muted rounded-xl border border-border flex items-center justify-between text-xs"
          >
            <div>
              <div class="font-mono font-bold text-text-main">{{ p.booking_code }}</div>
              <div class="font-semibold text-text-main text-[11px]">{{ p.customer_name }} → {{ p.partner_name }}</div>
              <div class="text-text-muted text-[10px]">{{ p.bank_sender }} • {{ p.created_at }}</div>
            </div>
            <div class="text-right">
              <div class="font-black text-primary text-sm">{{ formatRupiah(p.amount) }}</div>
              <Link
                :href="route('admin.payments.show', p.id)"
                class="mt-1 inline-block px-2.5 py-1 bg-primary text-white rounded-lg text-[10px] font-bold hover:bg-primary-dark transition"
              >
                Verifikasi
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Recent Bookings -->
      <div class="bg-surface rounded-2xl border border-border p-6 shadow-card space-y-4">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <div class="flex items-center gap-2 font-bold text-base text-text-main">
            <span>🎒</span>
            <span>Aktivitas Pesanan Terbaru</span>
          </div>
          <span class="text-xs font-bold text-text-muted">Total: {{ metrics.total_bookings }} Pesanan</span>
        </div>

        <div v-if="recentBookings.length === 0" class="py-8 text-center text-text-muted text-xs">
          Belum ada transaksi pemesanan di marketplace.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="b in recentBookings"
            :key="b.id"
            class="p-3.5 bg-surface-muted rounded-xl border border-border flex items-center justify-between text-xs"
          >
            <div>
              <div class="flex items-center gap-2">
                <span class="font-mono font-bold text-text-main">{{ b.booking_code }}</span>
                <span
                  :class="{
                    'bg-amber-100 text-amber-900': b.status === 'PENDING_APPROVAL',
                    'bg-purple-100 text-purple-900': b.status === 'MANUAL_REVIEW',
                    'bg-blue-100 text-blue-900': b.status === 'AWAITING_PAYMENT',
                    'bg-emerald-100 text-emerald-900': b.status === 'CONFIRMED',
                    'bg-rose-100 text-danger': ['CANCELLED', 'EXPIRED', 'REJECTED'].includes(b.status),
                  }"
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                >
                  {{ b.status_label }}
                </span>
              </div>
              <div class="text-[11px] text-text-muted mt-0.5">
                {{ b.customer_name }} • Mitra: {{ b.partner_name }}
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold text-text-main">{{ formatRupiah(b.total_price) }}</div>
              <div class="text-[10px] text-text-muted">{{ b.created_at }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
