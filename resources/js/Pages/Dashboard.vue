<script setup>
import { formatRupiah, getInitials } from '@/Composables/useFormat';
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTrans } from '@/Composables/useTrans';

const { t, locale } = useTrans();

const props = defineProps({
  userProfile: Object,
  metrics: Object,
  actionRequired: Array,
  upcomingTrips: Array,
  recentBookings: Array,
  featuredDestinations: Array,
  featuredTours: Array,
});

const user = computed(() => props.userProfile || {});

const userInitials = computed(() => getInitials(user.value.name));

</script>

<template>
  <AppLayout>
    <Head :title="t('dashboard.title')" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Top Section: Welcome Banner & User Profile Identity Card -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Welcome Hero Banner -->
        <div class="lg:col-span-7 xl:col-span-8 relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary to-primary-dark text-white p-6 sm:p-8 shadow-card flex flex-col justify-between">
          <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold mb-3">
              <span>🌿</span>
              <span>{{ t('dashboard.welcome_badge') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
              {{ t('dashboard.greeting', { name: user.name || $page.props.auth.user.name }) }}
            </h1>
            <p class="text-white/80 text-sm mt-1 sm:mt-2">
              {{ t('dashboard.subtitle') }}
            </p>

            <div class="flex flex-wrap gap-3 mt-6">
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

        <!-- User Profile Photo & Personal Data Card -->
        <div class="lg:col-span-5 xl:col-span-4 bg-surface rounded-3xl border border-border p-6 shadow-card flex flex-col justify-between">
          <div>
            <!-- Header Label & Verified Badge -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pb-3 border-b border-border">
              <span class="text-xs font-bold uppercase tracking-wider text-text-muted">Profil Wisatawan</span>
              <span class="inline-flex self-start sm:self-auto items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Status: {{ user.role_label || 'Wisatawan Aktif' }}</span>
              </span>
            </div>

            <!-- Avatar & Main Identifiers -->
            <div class="flex items-center gap-4 mt-4">
              <div class="relative">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/10 to-primary/20 border-2 border-primary/30 text-primary font-black text-lg flex items-center justify-center uppercase shadow-2xs">
                  {{ userInitials }}
                </div>
                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white" title="Aktif"></span>
              </div>
              <div class="min-w-0">
                <h2 class="text-base font-extrabold text-text-main truncate">
                  {{ user.name }}
                </h2>
                <div class="text-xs text-text-muted truncate">
                  {{ user.email }}
                </div>
              </div>
            </div>

            <!-- Personal Data List -->
            <div class="mt-4 space-y-2 text-xs">
              <div class="flex items-center justify-between p-2.5 bg-surface-muted/60 rounded-xl border border-border/60">
                <span class="text-text-muted flex items-center gap-1.5">
                  <span>📱</span>
                  <span>WhatsApp / HP:</span>
                </span>
                <span class="font-bold text-text-main font-mono">
                  {{ user.phone || 'Belum diatur' }}
                </span>
              </div>

              <div class="flex items-center justify-between p-2.5 bg-surface-muted/60 rounded-xl border border-border/60">
                <span class="text-text-muted flex items-center gap-1.5">
                  <span>✉️</span>
                  <span>Verifikasi Email:</span>
                </span>
                <span
                  class="font-semibold text-[11px] px-2 py-0.5 rounded-md"
                  :class="user.email_verified ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                >
                  {{ user.email_verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                </span>
              </div>

              <div class="flex items-center justify-between p-2.5 bg-surface-muted/60 rounded-xl border border-border/60">
                <span class="text-text-muted flex items-center gap-1.5">
                  <span>🗓️</span>
                  <span>Bergabung:</span>
                </span>
                <span class="font-semibold text-text-main">
                  {{ user.joined_at }}
                </span>
              </div>
            </div>
          </div>

          <!-- Edit Profile CTA -->
          <div class="pt-4 mt-4 border-t border-border">
            <Link
              href="/profile"
              class="w-full py-2.5 px-3 rounded-xl border border-border hover:border-primary/50 text-text-main hover:text-primary hover:bg-primary/5 text-xs font-bold transition flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
              <span>Perbarui Profil & Kata Sandi</span>
            </Link>
          </div>
        </div>
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
