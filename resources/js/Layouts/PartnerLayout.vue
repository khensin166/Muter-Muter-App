<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const page = usePage();
const user = page.props.auth ? page.props.auth.user : null;
const isSidebarOpen = ref(false);
</script>

<template>
  <div class="min-h-screen bg-background text-text-main flex flex-col font-sans">
    <!-- Top Header Bar -->
    <header class="bg-surface border-b border-border sticky top-0 z-40">
      <div class="px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
        <div class="flex items-center gap-4">
          <button
            @click="isSidebarOpen = !isSidebarOpen"
            class="md:hidden p-2 rounded-lg text-text-muted hover:bg-surface-muted"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="flex items-center gap-3">
            <ApplicationLogo className="h-8 w-auto" />
            <span class="text-xs font-bold uppercase tracking-wider px-2 py-1 bg-emerald-100 text-emerald-800 rounded-md">Mitra Vendor</span>
            <span class="text-sm font-semibold text-text-main hidden sm:inline">Portal Operasional Mitra Wisata</span>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <LanguageSwitcher />
          <Link href="/" class="text-xs font-medium text-text-muted hover:text-primary hidden sm:inline">
            ← Ke Marketplace
          </Link>
          <div class="text-right">
            <div class="text-sm font-bold text-text-main">{{ user?.name || 'Mitra' }}</div>
            <div class="text-xs text-text-muted">Status: Terverifikasi</div>
          </div>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-border text-text-muted hover:text-danger hover:border-danger/30 transition"
          >
            Keluar
          </Link>
        </div>
      </div>
    </header>

    <div class="flex-1 flex">
      <!-- Left Sidebar -->
      <aside
        :class="[
          'bg-surface border-r border-border w-64 p-4 space-y-1 shrink-0 transition-transform duration-200 z-30',
          'fixed inset-y-0 left-0 top-16 md:static md:translate-x-0',
          isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]"
      >
        <div class="text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          Operasional
        </div>
        <Link
          href="/partner"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>📊</span>
          <span>Ringkasan & SLA</span>
        </Link>
        <Link
          href="/partner/bookings"
          class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <div class="flex items-center gap-3">
            <span>📥</span>
            <span>Permintaan Booking</span>
          </div>
          <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-bold">24h SLA</span>
        </Link>
        <Link
          href="/partner/schedule"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🗓️</span>
          <span>Jadwal Harian & Armada</span>
        </Link>

        <div class="pt-4 text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          Katalog Produk
        </div>
        <Link
          href="/partner/products"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🏷️</span>
          <span>Semua Produk</span>
        </Link>
        <Link
          href="/partner/products?type=TOUR"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🎒</span>
          <span>Paket Wisata</span>
        </Link>
        <Link
          href="/partner/products?type=VEHICLE"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🚐</span>
          <span>Armada Rental</span>
        </Link>

        <div class="pt-4 text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          Manajemen Usaha
        </div>
        <Link
          href="/partner/staff"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>👥</span>
          <span>Staf & Hak Akses</span>
        </Link>
        <Link
          href="/partner/settlements"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>💰</span>
          <span>Pencairan & Settlement</span>
        </Link>
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 p-6 md:p-8 max-w-7xl">
        <slot />
      </main>
    </div>
  </div>
</template>

