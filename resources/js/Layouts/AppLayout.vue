<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = page.props.auth ? page.props.auth.user : null;
</script>

<template>
  <div class="min-h-screen bg-background text-text-main flex flex-col font-sans selection:bg-accent selection:text-white">
    <!-- Top Alert / Info Bar -->
    <div class="bg-primary-dark text-white text-xs py-1.5 px-4 text-center">
      <span>Jelajahi keindahan Sumatera Utara dengan pemesanan terintegrasi, transparan, dan terpercaya.</span>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-50 bg-surface border-b border-border shadow-xs backdrop-blur-md bg-opacity-95">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <!-- Logo & Brand -->
          <div class="flex items-center gap-8">
            <Link :href="route('home', {}, false) ? '/' : '/'" class="flex items-center gap-2 group">
              <ApplicationLogo className="h-9 sm:h-10 w-auto group-hover:scale-105 transition-transform" />
            </Link>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
              <Link href="/" class="text-text-main hover:text-primary transition-colors">Eksplor Destinasi</Link>
              <Link href="/tours" class="text-text-main hover:text-primary transition-colors">Paket Wisata</Link>
              <Link href="/vehicles" class="text-text-main hover:text-primary transition-colors">Rental Kendaraan</Link>
              <Link href="/custom-trip" class="text-text-main hover:text-primary transition-colors">Custom Trip</Link>
            </nav>
          </div>

          <!-- Auth & CTA Section -->
          <div class="hidden md:flex items-center gap-4">
            <template v-if="user">
              <Link
                v-if="user.role === 'ADMIN' || user.role === 'SUPER_ADMIN'"
                href="/admin"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition"
              >
                Admin Panel
              </Link>
              <Link
                v-else-if="user.role === 'PARTNER'"
                href="/partner"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition"
              >
                Portal Mitra
              </Link>
              <Link
                :href="route('dashboard')"
                class="text-sm font-medium text-text-main hover:text-primary transition"
              >
                Dashboard
              </Link>
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-sm font-medium text-text-muted hover:text-danger transition"
              >
                Keluar
              </Link>
            </template>
            <template v-else>
              <Link
                :href="route('login')"
                class="text-sm font-medium text-text-main hover:text-primary transition"
              >
                Masuk
              </Link>
              <Link
                :href="route('register')"
                class="bg-accent hover:bg-accent-hover text-white text-sm font-semibold py-2 px-4 rounded-xl shadow-sm transition"
              >
                Daftar
              </Link>
            </template>
          </div>

          <!-- Mobile Hamburger -->
          <div class="flex items-center md:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="p-2 rounded-lg text-text-muted hover:text-text-main hover:bg-surface-muted transition"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                  :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Dropdown Menu -->
      <div v-show="showingNavigationDropdown" class="md:hidden border-b border-border bg-surface px-4 pt-2 pb-4 space-y-2">
        <Link href="/" class="block py-2 text-sm font-medium text-text-main hover:text-primary">Eksplor Destinasi</Link>
        <Link href="/tours" class="block py-2 text-sm font-medium text-text-main hover:text-primary">Paket Wisata</Link>
        <Link href="/vehicles" class="block py-2 text-sm font-medium text-text-main hover:text-primary">Rental Kendaraan</Link>
        <Link href="/custom-trip" class="block py-2 text-sm font-medium text-text-main hover:text-primary">Custom Trip</Link>
        <div class="pt-3 border-t border-border flex flex-col gap-2">
          <template v-if="user">
            <Link :href="route('dashboard')" class="block py-2 text-sm font-medium text-primary">Dashboard ({{ user.name }})</Link>
            <Link :href="route('logout')" method="post" as="button" class="text-left py-2 text-sm text-danger font-medium">Keluar</Link>
          </template>
          <template v-else>
            <Link :href="route('login')" class="block py-2 text-sm font-medium text-text-main">Masuk</Link>
            <Link :href="route('register')" class="block py-2 text-center bg-accent text-white rounded-lg font-semibold text-sm">Daftar Sekarang</Link>
          </template>
        </div>
      </div>
    </header>

    <!-- Page Content Slot -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Global Footer -->
    <footer class="bg-primary-dark text-white pt-12 pb-8 border-t border-primary/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="space-y-3">
          <Link href="/" class="inline-block">
            <ApplicationLogo variant="white" className="h-10 w-auto" />
          </Link>
          <p class="text-sm text-slate-300 leading-relaxed">
            Platform perjalanan wisata terintegrasi untuk menemukan pesona Sumatera Utara — Danau Toba, Bukit Lawang, Tangkahan, Berastagi, Medan, dan sekitarnya.
          </p>
        </div>
        <div>
          <h4 class="text-sm font-bold uppercase tracking-wider text-accent mb-3">Wilayah Wisata</h4>
          <ul class="space-y-2 text-sm text-slate-300">
            <li><Link href="/destinations?cluster=TOBA" class="hover:text-white transition">Danau Toba & Samosir</Link></li>
            <li><Link href="/destinations?cluster=BUKIT_LAWANG" class="hover:text-white transition">Bukit Lawang (Orangutan)</Link></li>
            <li><Link href="/destinations?cluster=TANGKAHAN" class="hover:text-white transition">Tangkahan Eco-Park (Gajah)</Link></li>
            <li><Link href="/destinations?cluster=BERASTAGI" class="hover:text-white transition">Berastagi & Dataran Tinggi Karo</Link></li>
            <li><Link href="/destinations?cluster=MEDAN" class="hover:text-white transition">Medan City & Transit Hub</Link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-sm font-bold uppercase tracking-wider text-accent mb-3">Layanan Kami</h4>
          <ul class="space-y-2 text-sm text-slate-300">
            <li><Link href="/tours" class="hover:text-white transition">Paket Tour (One-day & Open Trip)</Link></li>
            <li><Link href="/vehicles" class="hover:text-white transition">Rental Mobil & Minibus</Link></li>
            <li><Link href="/custom-trip" class="hover:text-white transition">Custom Trip & Rombongan</Link></li>
            <li><Link :href="route('partner.register')" class="hover:text-white transition text-accent font-semibold">Gabung Jadi Mitra Vendor &rarr;</Link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-sm font-bold uppercase tracking-wider text-accent mb-3">Bantuan & Kontak</h4>
          <p class="text-sm text-slate-300 leading-relaxed mb-3">
            Butuh konsultasi perjalanan atau bantuan reservasi?
          </p>
          <a
            href="https://wa.me/6281234567890"
            target="_blank"
            class="inline-flex items-center gap-2 bg-secondary hover:bg-secondary/90 text-white text-xs font-semibold py-2 px-3.5 rounded-lg transition"
          >
            <span>💬 Konsultasi via WhatsApp</span>
          </a>
        </div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-6 border-t border-white/10 text-center text-xs text-slate-400">
        © 2026 BetaTudia - Platform Wisata Sumatera Utara. Hak Cipta Dilindungi.
      </div>
    </footer>
  </div>
</template>

