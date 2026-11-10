<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTrans } from '@/Composables/useTrans';

const { t, locale } = useTrans();

const props = defineProps({
  metrics: Object,
  actionRequired: Array,
  upcomingTrips: Array,
  recentBookings: Array,
  featuredDestinations: Array,
  featuredTours: Array,
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
    <Head :title="t('dashboard.title')" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Welcome Header -->
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary to-primary-dark text-white p-6 sm:p-8 shadow-card">
        <div class="relative z-10 max-w-2xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold mb-3">
            <span>🌿</span>
            <span>{{ t('dashboard.welcome_badge') }}</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
            {{ t('dashboard.greeting', { name: $page.props.auth.user.name }) }}
          </h1>
          <p class="text-white/80 text-sm mt-1 sm:mt-2">
            {{ t('dashboard.subtitle') }}
          </p>

          <div class="flex flex-wrap gap-3 mt-5">
            <Link
              href="/tours"
              class="px-4 py-2 rounded-xl bg-accent hover:bg-accent-hover text-white text-xs font-bold shadow-sm transition inline-flex items-center gap-1.5"
            >
              <span>🔍</span>
              <span>{{ t('dashboard.find_tours') }}</span>
            </Link>
            <Link
              href="/vehicles"
              class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold backdrop-blur-md border border-white/20 transition inline-flex items-center gap-1.5"
            >
              <span>🚗</span>
              <span>{{ t('dashboard.rent_vehicle') }}</span>
            </Link>
            <Link
              :href="route('bookings.my')"
              class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold backdrop-blur-md border border-white/20 transition inline-flex items-center gap-1.5"
            >
              <span>📋</span>
              <span>{{ t('dashboard.my_bookings') }}</span>
            </Link>
          </div>
        </div>

        <!-- Decorative background ring -->
        <div class="absolute -right-16 -bottom-24 w-72 h-72 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
      </div>

      <!-- Action Required (Pending Payments / Upload Proof) -->
      <div v-if="actionRequired && actionRequired.length > 0" class="space-y-3">
        <div class="flex items-center gap-2">
          <span class="text-lg">⚠️</span>
          <h2 class="text-base font-bold text-text-main">{{ t('dashboard.action_required_title') }}</h2>
          <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black uppercase">
            {{ actionRequired.length }} {{ locale === 'en' ? 'Pending' : 'Menunggu' }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="item in actionRequired"
            :key="item.id"
            class="p-5 bg-amber-50/70 border border-amber-200 rounded-2xl flex flex-col justify-between gap-4 shadow-xs"
          >
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="font-mono text-xs font-bold text-primary">{{ item.booking_code }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-200/80 text-amber-900">
                  {{ item.payment_status_label }}
                </span>
              </div>
              <h3 class="font-bold text-text-main text-sm line-clamp-1">{{ item.product_name }}</h3>
              <p class="text-xs text-text-muted mt-0.5">{{ t('dashboard.departure_date') }}: {{ item.service_start_date }}</p>
              
              <div class="mt-3 p-3 bg-white/80 rounded-xl border border-amber-200/60 flex items-center justify-between">
                <span class="text-xs text-text-muted">Target DP:</span>
                <span class="text-sm font-black text-primary">{{ formatRupiah(item.dp_amount) }}</span>
              </div>
            </div>

            <Link
              :href="route('bookings.show', item.booking_code)"
              class="w-full text-center py-2 px-3 bg-accent hover:bg-accent-hover text-white text-xs font-bold rounded-xl shadow-xs transition"
            >
              {{ t('dashboard.upload_proof_btn') }} &rarr;
            </Link>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
          <div class="flex items-center justify-between text-text-muted">
            <span class="text-xs font-semibold">{{ t('dashboard.upcoming_trips') }}</span>
            <span class="text-base">🎒</span>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-text-main">
            {{ metrics.active_trips }}
          </div>
          <span class="text-[11px] text-emerald-600 font-semibold block">{{ locale === 'en' ? 'Ready to experience' : 'Siap dinikmati' }}</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
          <div class="flex items-center justify-between text-text-muted">
            <span class="text-xs font-semibold">{{ t('dashboard.awaiting_payment') }}</span>
            <span class="text-base">💳</span>
          </div>
          <div class="text-2xl sm:text-3xl font-black" :class="metrics.pending_payment > 0 ? 'text-amber-600' : 'text-text-main'">
            {{ metrics.pending_payment }}
          </div>
          <span class="text-[11px] text-text-muted block">{{ locale === 'en' ? 'Hold 24 hours' : 'Batas hold 24 jam' }}</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
          <div class="flex items-center justify-between text-text-muted">
            <span class="text-xs font-semibold">{{ t('dashboard.completed_trips') }}</span>
            <span class="text-base">✨</span>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-text-main">
            {{ metrics.completed_trips }}
          </div>
          <span class="text-[11px] text-primary font-semibold block">{{ locale === 'en' ? 'Memorable journeys' : 'Pengalaman terukir' }}</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-1">
          <div class="flex items-center justify-between text-text-muted">
            <span class="text-xs font-semibold">{{ t('dashboard.active_bookings') }}</span>
            <span class="text-base">📄</span>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-text-main">
            {{ metrics.total_bookings }}
          </div>
          <span class="text-[11px] text-text-muted block">{{ locale === 'en' ? 'Platform activity' : 'Aktivitas di platform' }}</span>
        </div>
      </div>

      <!-- Main Columns: Upcoming Trips & Recent Bookings -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Upcoming Confirmed Trips (1 Col) -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-text-main">Jadwal Trip Mendatang</h2>
            <span class="text-xs text-text-muted">Confirmed</span>
          </div>

          <div v-if="upcomingTrips && upcomingTrips.length > 0" class="space-y-3">
            <div
              v-for="trip in upcomingTrips"
              :key="trip.id"
              class="p-5 bg-surface border border-emerald-200 rounded-2xl shadow-xs space-y-3 relative overflow-hidden"
            >
              <div class="absolute top-0 right-0 w-2 h-full bg-emerald-500"></div>
              <div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md uppercase">
                  Terkonfirmasi Mitra
                </span>
                <h3 class="font-bold text-text-main text-sm mt-1.5">{{ trip.product_name }}</h3>
                <p class="text-xs text-text-muted mt-0.5">Mitra: {{ trip.partner_name }}</p>
              </div>

              <div class="p-3 bg-surface-muted rounded-xl text-xs space-y-1">
                <div class="flex items-center justify-between text-text-main">
                  <span class="text-text-muted">Tanggal:</span>
                  <span class="font-bold">{{ trip.service_start_date }}</span>
                </div>
                <div v-if="trip.service_start_time" class="flex items-center justify-between text-text-main">
                  <span class="text-text-muted">Waktu Kumpul:</span>
                  <span class="font-bold">{{ trip.service_start_time }} WIB</span>
                </div>
              </div>

              <div class="flex items-center justify-between pt-1">
                <a
                  v-if="trip.partner_phone"
                  :href="`https://wa.me/${trip.partner_phone.replace(/\D/g, '')}`"
                  target="_blank"
                  class="text-xs text-emerald-700 hover:text-emerald-800 font-bold inline-flex items-center gap-1"
                >
                  <span>💬</span> Kontak Mitra
                </a>
                <Link
                  :href="route('bookings.show', trip.booking_code)"
                  class="text-xs font-bold text-primary hover:underline ml-auto"
                >
                  Lihat Tiket &rarr;
                </Link>
              </div>
            </div>
          </div>

          <div v-else class="p-8 bg-surface rounded-2xl border border-dashed border-border text-center space-y-3">
            <div class="text-3xl">🏞️</div>
            <p class="text-xs text-text-muted">Belum ada trip aktif yang dijadwalkan.</p>
            <Link
              href="/tours"
              class="inline-block text-xs font-bold text-primary hover:underline"
            >
              Jelajahi Paket Wisata Danau Toba &rarr;
            </Link>
          </div>
        </div>

        <!-- Right: Recent Bookings Overview (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-text-main">Pesanan Terakhir</h2>
            <Link :href="route('bookings.my')" class="text-xs font-bold text-primary hover:underline">
              Lihat Semua &rarr;
            </Link>
          </div>

          <div v-if="recentBookings && recentBookings.length > 0" class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
            <div class="divide-y divide-border">
              <div
                v-for="b in recentBookings"
                :key="b.id"
                class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-surface-muted/50 transition"
              >
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-primary">{{ b.booking_code }}</span>
                    <span
                      :class="{
                        'bg-amber-100 text-amber-900': b.status === 'PENDING_APPROVAL',
                        'bg-purple-100 text-purple-900': b.status === 'MANUAL_REVIEW',
                        'bg-blue-100 text-blue-900': b.status === 'AWAITING_PAYMENT',
                        'bg-emerald-100 text-emerald-900': b.status === 'CONFIRMED' || b.status === 'COMPLETED',
                        'bg-rose-100 text-danger': ['REJECTED', 'CANCELLED', 'EXPIRED'].includes(b.status),
                      }"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                    >
                      {{ b.status_label }}
                    </span>
                  </div>
                  <h4 class="font-bold text-sm text-text-main">{{ b.product_name }}</h4>
                  <p class="text-xs text-text-muted">
                    {{ b.partner_name }} • Tanggal Pelaksanaan: <span class="font-semibold text-text-main">{{ b.service_start_date }}</span>
                  </p>
                </div>

                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-2 sm:pt-0 border-border">
                  <span class="text-xs text-text-muted sm:text-[11px]">Total Tagihan:</span>
                  <span class="text-sm font-black text-text-main">{{ formatRupiah(b.total_price) }}</span>
                  <Link
                    :href="route('bookings.show', b.booking_code)"
                    class="mt-1 text-xs font-bold text-accent hover:underline"
                  >
                    Detail &rarr;
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="p-8 bg-surface rounded-2xl border border-dashed border-border text-center space-y-3">
            <div class="text-3xl">🎫</div>
            <p class="text-xs text-text-muted">Anda belum memiliki riwayat pemesanan.</p>
            <Link
              href="/"
              class="inline-block text-xs font-bold text-primary hover:underline"
            >
              Mulai Eksplorasi Danau Toba &rarr;
            </Link>
          </div>
        </div>
      </div>

      <!-- Inspiration Section: Top Destinations & Tours -->
      <div class="pt-6 border-t border-border space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-black text-text-main">Destinasi Pilihan di Sumatera Utara</h2>
            <p class="text-xs text-text-muted">Kunjungi spot ikonik di Danau Toba, Bukit Lawang, Tangkahan, Berastagi, dan Medan.</p>
          </div>
          <Link href="/destinations" class="text-xs font-bold text-primary hover:underline">
            Lihat Semua &rarr;
          </Link>
        </div>

        <div v-if="featuredDestinations && featuredDestinations.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            v-for="d in featuredDestinations"
            :key="d.slug"
            :href="route('destinations.show', d.slug)"
            class="group bg-surface rounded-2xl border border-border overflow-hidden shadow-xs hover:shadow-card hover:-translate-y-0.5 transition"
          >
            <div class="h-28 bg-surface-muted overflow-hidden relative">
              <img
                v-if="d.image"
                :src="d.image"
                :alt="d.name"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-primary/10 text-primary font-bold text-sm">
                Sumatera Utara
              </div>
              <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md bg-black/60 backdrop-blur-sm text-[10px] font-bold text-white">
                {{ d.cluster }}
              </span>
            </div>
            <div class="p-3">
              <h3 class="font-bold text-xs text-text-main group-hover:text-primary transition line-clamp-1">
                {{ d.name }}
              </h3>
              <p class="text-[11px] text-text-muted mt-0.5">
                {{ d.ticket_price > 0 ? formatRupiah(d.ticket_price) : 'Gratis / Bebas Akses' }}
              </p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
