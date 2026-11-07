<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  bookings: Object,
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
  <AppLayout>
    <Head title="Pesanan Saya — BetaTudia" />

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">Dashboard Wisatawan</span>
          <h1 class="text-2xl font-black text-text-main">Riwayat Pesanan Saya</h1>
          <p class="text-xs text-text-muted mt-0.5">Pantau status konfirmasi mitra vendor dan tagihan DP perjalanan Anda.</p>
        </div>

        <Link
          href="/tours"
          class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold hover:bg-primary-dark transition shadow-sm"
        >
          + Pesan Trip Baru
        </Link>
      </div>

      <!-- Bookings List -->
      <div v-if="bookings.data.length > 0" class="space-y-4">
        <div
          v-for="b in bookings.data"
          :key="b.id"
          class="p-5 bg-surface rounded-2xl border border-border shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div>
            <div class="flex items-center gap-2 mb-1.5">
              <span class="font-mono text-xs font-bold text-primary">{{ b.booking_code }}</span>
              <span
                :class="{
                  'bg-amber-100 text-amber-900': b.booking_status === 'PENDING_APPROVAL',
                  'bg-purple-100 text-purple-900': b.booking_status === 'MANUAL_REVIEW',
                  'bg-blue-100 text-blue-900': b.booking_status === 'AWAITING_PAYMENT',
                  'bg-emerald-100 text-emerald-900': b.booking_status === 'CONFIRMED',
                  'bg-rose-100 text-danger': ['REJECTED', 'CANCELLED', 'EXPIRED'].includes(b.booking_status),
                }"
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
              >
                {{ b.booking_status_label }}
              </span>
            </div>

            <h3 class="font-bold text-text-main text-base">{{ b.product_name }}</h3>
            <div class="mt-1 text-xs text-text-muted flex items-center gap-3">
              <span>📅 Berangkat: {{ b.service_start_date }}</span>
              <span>🏢 Mitra: {{ b.partner_name }}</span>
            </div>
          </div>

          <div class="flex sm:flex-col items-center sm:items-end justify-between border-t sm:border-t-0 pt-3 sm:pt-0 border-border gap-2">
            <div>
              <span class="text-[10px] uppercase text-text-muted block text-right font-bold">Total Biaya</span>
              <span class="text-base font-black text-primary">{{ formatRupiah(b.total_price) }}</span>
            </div>

            <Link
              :href="route('bookings.show', b.booking_code)"
              class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold transition shadow-xs"
            >
              Lihat Rincian →
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-surface rounded-2xl border border-border">
        <span class="text-4xl">🎒</span>
        <h3 class="text-lg font-bold text-text-main mt-3">Belum ada pemesanan</h3>
        <p class="text-xs text-text-muted mt-1">Anda belum memiliki pemesanan aktif. Jelajahi katalog paket tour dan sewa kendaraan kami.</p>
        <div class="mt-4 flex justify-center gap-3">
          <Link href="/tours" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl">
            Lihat Paket Tour
          </Link>
          <Link href="/vehicles" class="px-4 py-2 bg-secondary text-white text-xs font-bold rounded-xl">
            Sewa Kendaraan
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

