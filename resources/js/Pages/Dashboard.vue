<script setup>
import { formatRupiah, getInitials } from '@/Composables/useFormat';
import { getBookingStatusInfo } from '@/Composables/useStatusBadge';
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8">
      <!-- Customer Hub Sub-Navigation -->
      <nav
        aria-label="Navigasi Akun Wisatawan"
        c lass="flex items-center justify-between gap-4 border-b border-border/80 pb-4"
      >
        <div class="flex items-center gap-1.5 bg-surface p-1.5 rounded-2xl border border-border shadow-2xs overflow-x-auto max-w-full">
          <Link
            :href="route('dashboard')"
            aria-current="page"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-primary text-white shadow-xs whitespace-nowrap tactile-press"
          > 
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            <span>Ringkasan</span>
          </Link>

          <Link
            :href="route('bookings.my')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-text-muted hover:text-text-main hover:bg-surface-muted transition whitespace-nowrap tactile-press"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
            </svg>
            <span>{{ t('dashboard.my_bookings') }}</span>
            <span
              v-if="metrics?.pending_payment > 0"
              class="px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black tabular-nums leading-none"
            >
              {{ metrics.pending_payment }}
            </span>
          </Link>

          <Link
            href="/profile"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-text-muted hover:text-text-main hover:bg-surface-muted transition whitespace-nowrap tactile-press"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            <span>Profil &amp; Keamanan</span>
          </Link>
        </div>

        <Link
          href="/destinations"
          class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:text-primary-dark transition whitespace-nowrap tactile-press"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
          </svg>
          <span>{{ t('nav.explore_destinations') }}</span>
          <span aria-hidden="true">&rarr;</span>
        </Link>
      </nav>

      <!-- Top Section: Welcome Banner & User Profile Identity Card -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Welcome Hero Banner -->
        <div class="lg:col-span-7 xl:col-span-8 relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-primary-dark text-white p-6 sm:p-8 shadow-card flex flex-col justify-between">
          <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/15 backdrop-blur-md text-xs font-semibold mb-3">
              <svg class="w-3.5 h-3.5 text-accent shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
              </svg>
              <span>{{ t('dashboard.welcome_badge') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-[-0.02em] leading-tight">
              {{ t('dashboard.greeting', { name: user.name || $page.props.auth.user.name }) }}
            </h1>
            <p class="text-white/85 text-sm mt-1.5 sm:mt-2 leading-relaxed">
              {{ t('dashboard.subtitle') }}
            </p>

            <div class="flex flex-wrap gap-2.5 mt-6">
              <Link
                href="/destinations"
                class="px-4 py-2.5 rounded-xl bg-accent hover:bg-accent-hover text-white text-xs font-bold shadow-sm transition inline-flex items-center gap-1.5 tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span>{{ t('nav.explore_destinations') }}</span>
              </Link>
              <Link
                href="/tours"
                class="px-4 py-2.5 rounded-xl bg-white/12 hover:bg-white/20 text-white text-xs font-bold backdrop-blur-md border border-white/20 transition inline-flex items-center gap-1.5 tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <span>{{ t('dashboard.find_tours') }}</span>
              </Link>
              <Link
                href="/vehicles"
                class="px-4 py-2.5 rounded-xl bg-white/12 hover:bg-white/20 text-white text-xs font-bold backdrop-blur-md border border-white/20 transition inline-flex items-center gap-1.5 tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <span>{{ t('dashboard.rent_vehicle') }}</span>
              </Link>
              <Link
                :href="route('bookings.my')"
                class="px-4 py-2.5 rounded-xl bg-white/12 hover:bg-white/20 text-white text-xs font-bold backdrop-blur-md border border-white/20 transition inline-flex items-center gap-1.5 tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
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
              <span class="inline-flex self-start sm:self-auto items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Status: {{ user.role_label || 'Wisatawan Aktif' }}</span>
              </span>
            </div>

            <!-- Avatar & Main Identifiers -->
            <div class="flex items-center gap-4 mt-4">
              <div class="relative shrink-0">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/10 to-primary/20 border-2 border-primary/25 text-primary font-black text-lg flex items-center justify-center uppercase shadow-2xs">
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
                <span class="text-text-muted flex items-center gap-2">
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                  </svg>
                  <span>WhatsApp / HP:</span>
                </span>
                <span class="font-bold text-text-main font-mono tabular-nums">
                  {{ user.phone || 'Belum diatur' }}
                </span>
              </div>

              <div class="flex items-center justify-between p-2.5 bg-surface-muted/60 rounded-xl border border-border/60">
                <span class="text-text-muted flex items-center gap-2">
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                  </svg>
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
                <span class="text-text-muted flex items-center gap-2">
                  <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                  <span>Bergabung:</span>
                </span>
                <span class="font-semibold text-text-main tabular-nums">
                  {{ user.joined_at }}
                </span>
              </div>
            </div>
          </div>

          <!-- Edit Profile CTA -->
          <div class="pt-4 mt-4 border-t border-border">
            <Link
              href="/profile"
              class="w-full py-2.5 px-3 rounded-xl border border-border hover:border-primary/50 text-text-main hover:text-primary hover:bg-primary/5 text-xs font-bold transition flex items-center justify-center gap-2 tactile-press"
            >
              <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
              <span>Perbarui Profil &amp; Kata Sandi</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Action Required (Pending Payments / Upload Proof) -->
      <div v-if="actionRequired && actionRequired.length > 0" class="space-y-3">
        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
          </div>
          <h2 class="text-base font-bold text-text-main">{{ t('dashboard.action_required_title') }}</h2>
          <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black uppercase tabular-nums">
            {{ actionRequired.length }} {{ locale === 'en' ? 'Pending' : 'Menunggu' }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="item in actionRequired"
            :key="item.id"
            class="p-5 bg-amber-50/70 border border-amber-200 rounded-2xl flex flex-col justify-between gap-4 shadow-xs hover-card-lift"
          >
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="font-mono text-xs font-bold text-primary">{{ item.booking_code }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-200/80 text-amber-900">
                  {{ item.payment_status_label }}
                </span>
              </div>
              <h3 class="font-bold text-text-main text-sm line-clamp-1">{{ item.product_name }}</h3>
              <p class="text-xs text-text-muted mt-0.5 tabular-nums">{{ t('dashboard.departure_date') }}: {{ item.service_start_date }}</p>

              <div class="mt-3 p-3 bg-white/85 rounded-xl border border-amber-200/60 flex items-center justify-between">
                <span class="text-xs text-text-muted">Target DP:</span>
                <span class="text-sm font-black text-primary tabular-nums">{{ formatRupiah(item.dp_amount) }}</span>
              </div>
            </div>

            <Link
              :href="route('bookings.show', item.booking_code)"
              class="w-full text-center py-2.5 px-3 bg-accent hover:bg-accent-hover text-white text-xs font-bold rounded-xl shadow-xs transition tactile-press"
            >
              {{ t('dashboard.upload_proof_btn') }} &rarr;
            </Link>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-2 hover:border-primary/25 transition-colors">
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-semibold text-text-muted">{{ t('dashboard.upcoming_trips') }}</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
              </svg>
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-text-main tabular-nums tracking-[-0.02em]">
            {{ metrics.active_trips }}
          </div>
          <span class="text-[11px] text-emerald-600 font-semibold block">{{ locale === 'en' ? 'Ready to experience' : 'Siap dinikmati' }}</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-2 hover:border-primary/25 transition-colors">
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-semibold text-text-muted">{{ t('dashboard.awaiting_payment') }}</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
              </svg>
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-black tabular-nums tracking-[-0.02em]" :class="metrics.pending_payment > 0 ? 'text-amber-600' : 'text-text-main'">
            {{ metrics.pending_payment }}
          </div>
          <span class="text-[11px] text-text-muted block">{{ locale === 'en' ? 'Hold 24 hours' : 'Batas hold 24 jam' }}</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-2 hover:border-primary/25 transition-colors">
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-semibold text-text-muted">{{ t('dashboard.completed_trips') }}</span>
            <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary border border-primary/15 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
              </svg>
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-text-main tabular-nums tracking-[-0.02em]">
            {{ metrics.completed_trips }}
          </div>
          <span class="text-[11px] text-primary font-semibold block">{{ locale === 'en' ? 'Memorable journeys' : 'Pengalaman terukir' }}</span>
        </div>

        <div class="p-5 bg-surface rounded-2xl border border-border shadow-xs space-y-2 hover:border-primary/25 transition-colors">
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-semibold text-text-muted">{{ t('dashboard.active_bookings') }}</span>
            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 border border-slate-200/80 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
              </svg>
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-text-main tabular-nums tracking-[-0.02em]">
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
            <h2 class="text-lg font-black text-text-main tracking-[-0.01em]">Jadwal Trip Mendatang</h2>
            <span class="text-xs font-medium text-text-muted">Confirmed</span>
          </div>

          <div v-if="upcomingTrips && upcomingTrips.length > 0" class="space-y-3">
            <div
              v-for="trip in upcomingTrips"
              :key="trip.id"
              class="p-5 bg-surface border border-emerald-200 rounded-2xl shadow-xs space-y-3 relative overflow-hidden hover-card-lift"
            >
              <div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>
              <div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-2 py-0.5 rounded-full uppercase">
                  Terkonfirmasi Mitra
                </span>
                <h3 class="font-bold text-text-main text-sm mt-2">{{ trip.product_name }}</h3>
                <p class="text-xs text-text-muted mt-0.5">Mitra: {{ trip.partner_name }}</p>
              </div>

              <div class="p-3 bg-surface-muted rounded-xl text-xs space-y-1">
                <div class="flex items-center justify-between text-text-main">
                  <span class="text-text-muted">Tanggal:</span>
                  <span class="font-bold tabular-nums">{{ trip.service_start_date }}</span>
                </div>
                <div v-if="trip.service_start_time" class="flex items-center justify-between text-text-main">
                  <span class="text-text-muted">Waktu Kumpul:</span>
                  <span class="font-bold tabular-nums">{{ trip.service_start_time }} WIB</span>
                </div>
              </div>

              <div class="flex items-center justify-between pt-1">
                <a
                  v-if="trip.partner_phone"
                  :href="`https://wa.me/${trip.partner_phone.replace(/\D/g, '')}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-xs text-emerald-700 hover:text-emerald-800 font-bold inline-flex items-center gap-1.5 tactile-press"
                >
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                  </svg>
                  <span>Kontak Mitra</span>
                </a>
                <Link
                  :href="route('bookings.show', trip.booking_code)"
                  class="text-xs font-bold text-primary hover:underline ml-auto tactile-press"
                >
                  Lihat Tiket &rarr;
                </Link>
              </div>
            </div>
          </div>

          <div v-else class="p-8 bg-surface rounded-2xl border border-dashed border-border text-center space-y-3">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
              </svg>
            </div>
            <p class="text-xs text-text-muted">Belum ada trip aktif yang dijadwalkan.</p>
            <Link
              href="/tours"
              class="inline-block text-xs font-bold text-primary hover:underline tactile-press"
            >
              Jelajahi Paket Wisata Danau Toba &rarr;
            </Link>
          </div>
        </div>

        <!-- Right: Recent Bookings Overview (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-text-main tracking-[-0.01em]">Pesanan Terakhir</h2>
            <Link :href="route('bookings.my')" class="text-xs font-bold text-primary hover:underline tactile-press">
              Lihat Semua &rarr;
            </Link>
          </div>

          <div v-if="recentBookings && recentBookings.length > 0" class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
            <div class="divide-y divide-border">
              <div
                v-for="b in recentBookings"
                :key="b.id"
                class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-surface-muted/50 transition-colors"
              >
                <div class="space-y-1 min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs font-bold text-primary">{{ b.booking_code }}</span>
                    <span
                      :class="getBookingStatusInfo(b.status).badgeClass"
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                    >
                      {{ b.status_label || getBookingStatusInfo(b.status).label }}
                    </span>
                  </div>
                  <h4 class="font-bold text-sm text-text-main truncate">{{ b.product_name }}</h4>
                  <p class="text-xs text-text-muted">
                    {{ b.partner_name }} • Tanggal Pelaksanaan:
                    <span class="font-semibold text-text-main tabular-nums">{{ b.service_start_date }}</span>
                  </p>
                </div>

                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-2 sm:pt-0 border-border shrink-0">
                  <span class="text-xs text-text-muted sm:text-[11px]">Total Tagihan:</span>
                  <span class="text-sm font-black text-text-main tabular-nums">{{ formatRupiah(b.total_price) }}</span>
                  <Link
                    :href="route('bookings.show', b.booking_code)"
                    class="mt-1 text-xs font-bold text-accent hover:underline tactile-press"
                  >
                    Detail &rarr;
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="p-8 bg-surface rounded-2xl border border-dashed border-border text-center space-y-3">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
              </svg>
            </div>
            <p class="text-xs text-text-muted">Anda belum memiliki riwayat pemesanan.</p>
            <Link
              href="/destinations"
              class="inline-block text-xs font-bold text-primary hover:underline tactile-press"
            >
              Mulai Eksplorasi Destinasi Sumatera Utara &rarr;
            </Link>
          </div>
        </div>
      </div>

      <!-- Featured Tours Section -->
      <div v-if="featuredTours && featuredTours.length > 0" class="pt-6 border-t border-border space-y-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h2 class="text-lg font-black text-text-main tracking-[-0.01em]">Paket Wisata Pilihan</h2>
            <p class="text-xs text-text-muted">Tur terkurasi dari mitra terpercaya di Sumatera Utara.</p>
          </div>
          <Link href="/tours" class="text-xs font-bold text-primary hover:underline shrink-0 tactile-press">
            Lihat Semua &rarr;
          </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <Link
            v-for="tItem in featuredTours"
            :key="tItem.slug"
            :href="route('tours.show', tItem.slug)"
            class="group bg-surface rounded-2xl border border-border overflow-hidden shadow-xs hover-card-lift tactile-press flex flex-col"
          >
            <div class="aspect-[16/10] bg-surface-muted overflow-hidden relative">
              <img
                v-if="tItem.image"
                :src="tItem.image"
                :alt="tItem.name"
                class="w-full h-full object-cover hover-card-media"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-primary/8 text-primary">
                <svg class="w-7 h-7 opacity-60" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
              </div>
              <div class="absolute inset-0 ring-1 ring-inset ring-black/5 pointer-events-none"></div>
            </div>
            <div class="p-3.5 flex-1 flex flex-col justify-between gap-1.5">
              <div>
                <p class="text-[11px] font-medium text-text-muted truncate">{{ tItem.partner_name }}</p>
                <h3 class="font-bold text-sm text-text-main group-hover:text-primary transition line-clamp-1 mt-0.5">
                  {{ tItem.name }}
                </h3>
              </div>
              <p class="text-xs font-extrabold text-primary tabular-nums pt-1">
                Mulai {{ formatRupiah(tItem.base_price) }}
              </p>
            </div>
          </Link>
        </div>
      </div>

      <!-- Inspiration Section: Top Destinations -->
      <div class="pt-6 border-t border-border space-y-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h2 class="text-lg font-black text-text-main tracking-[-0.01em]">Destinasi Pilihan di Sumatera Utara</h2>
            <p class="text-xs text-text-muted">Kunjungi spot ikonik di Danau Toba, Bukit Lawang, Tangkahan, Berastagi, dan Medan.</p>
          </div>
          <Link href="/destinations" class="text-xs font-bold text-primary hover:underline shrink-0 tactile-press">
            Lihat Semua &rarr;
          </Link>
        </div>

        <div v-if="featuredDestinations && featuredDestinations.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            v-for="d in featuredDestinations"
            :key="d.slug"
            :href="route('destinations.show', d.slug)"
            class="group bg-surface rounded-2xl border border-border overflow-hidden shadow-xs hover-card-lift tactile-press"
          >
            <div class="aspect-[16/10] bg-surface-muted overflow-hidden relative">
              <img
                v-if="d.image"
                :src="d.image"
                :alt="d.name"
                class="w-full h-full object-cover hover-card-media"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-primary/10 text-primary font-bold text-sm">
                Sumatera Utara
              </div>
              <div class="absolute inset-0 ring-1 ring-inset ring-black/5 pointer-events-none"></div>
              <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md bg-black/60 backdrop-blur-sm text-[10px] font-bold text-white">
                {{ d.cluster }}
              </span>
            </div>
            <div class="p-3">
              <h3 class="font-bold text-xs text-text-main group-hover:text-primary transition line-clamp-1">
                {{ d.name }}
              </h3>
              <p class="text-[11px] text-text-muted mt-0.5 tabular-nums">
                {{ d.ticket_price > 0 ? formatRupiah(d.ticket_price) : 'Gratis / Bebas Akses' }}
              </p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
