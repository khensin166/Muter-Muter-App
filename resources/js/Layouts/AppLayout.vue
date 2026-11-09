<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useTrans } from '@/Composables/useTrans';

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = page.props.auth ? page.props.auth.user : null;
const { t } = useTrans();
</script>

<template>
  <div class="min-h-screen bg-background text-text-main flex flex-col font-sans selection:bg-accent selection:text-white">
    <!-- Top Alert / Info Bar -->
    <div class="bg-primary-dark text-white text-xs py-1.5 px-4 text-center">
      <span>{{ t('nav.top_banner') }}</span>
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
              <Link href="/" class="text-text-main hover:text-primary transition-colors">{{ t('nav.explore_destinations') }}</Link>
              <Link href="/tours" class="text-text-main hover:text-primary transition-colors">{{ t('nav.tour_packages') }}</Link>
              <Link href="/vehicles" class="text-text-main hover:text-primary transition-colors">{{ t('nav.vehicle_rentals') }}</Link>
              <Link href="/custom-trip" class="text-text-main hover:text-primary transition-colors">{{ t('nav.custom_trip') }}</Link>
            </nav>
          </div>

          <!-- Auth & CTA Section -->
          <div class="hidden md:flex items-center gap-4">
            <LanguageSwitcher />

            <template v-if="user">
              <Link
                v-if="user.role === 'ADMIN' || user.role === 'SUPER_ADMIN'"
                href="/admin"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition"
              >
                {{ t('nav.admin_panel') }}
              </Link>
              <Link
                v-else-if="user.role === 'PARTNER'"
                href="/partner"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition"
              >
                {{ t('nav.partner_portal') }}
              </Link>
              <Link
                :href="route('dashboard')"
                class="text-sm font-medium text-text-main hover:text-primary transition"
              >
                {{ t('nav.dashboard') }}
              </Link>
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-sm font-medium text-text-muted hover:text-danger transition"
              >
                {{ t('nav.logout') }}
              </Link>
            </template>
            <template v-else>
              <Link
                :href="route('login')"
                class="text-sm font-medium text-text-main hover:text-primary transition"
              >
                {{ t('nav.login') }}
              </Link>
              <Link
                :href="route('register')"
                class="bg-accent hover:bg-accent-hover text-white text-sm font-semibold py-2 px-4 rounded-xl shadow-sm transition"
              >
                {{ t('nav.register') }}
              </Link>
            </template>
          </div>

          <!-- Mobile Hamburger -->
          <div class="flex items-center gap-2 md:hidden">
            <LanguageSwitcher />

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
        <Link href="/" class="block py-2 text-sm font-medium text-text-main hover:text-primary">{{ t('nav.explore_destinations') }}</Link>
        <Link href="/tours" class="block py-2 text-sm font-medium text-text-main hover:text-primary">{{ t('nav.tour_packages') }}</Link>
        <Link href="/vehicles" class="block py-2 text-sm font-medium text-text-main hover:text-primary">{{ t('nav.vehicle_rentals') }}</Link>
        <Link href="/custom-trip" class="block py-2 text-sm font-medium text-text-main hover:text-primary">{{ t('nav.custom_trip') }}</Link>
        <div class="pt-3 border-t border-border flex flex-col gap-2">
          <template v-if="user">
            <Link :href="route('dashboard')" class="block py-2 text-sm font-medium text-primary">{{ t('nav.dashboard') }} ({{ user.name }})</Link>
            <Link :href="route('logout')" method="post" as="button" class="text-left py-2 text-sm text-danger font-medium">{{ t('nav.logout') }}</Link>
          </template>
          <template v-else>
            <Link :href="route('login')" class="block py-2 text-sm font-medium text-text-main">{{ t('nav.login') }}</Link>
            <Link :href="route('register')" class="block py-2 text-center bg-accent text-white rounded-lg font-semibold text-sm">{{ t('nav.register_now') }}</Link>
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
            {{ t('footer.description') }}
          </p>
        </div>
        <div>
          <h4 class="text-sm font-bold uppercase tracking-wider text-accent mb-3">{{ t('footer.regions_title') }}</h4>
          <ul class="space-y-2 text-sm text-slate-300">
            <li><Link href="/destinations?cluster=TOBA" class="hover:text-white transition">{{ t('footer.region_toba') }}</Link></li>
            <li><Link href="/destinations?cluster=BUKIT_LAWANG" class="hover:text-white transition">{{ t('footer.region_bukit_lawang') }}</Link></li>
            <li><Link href="/destinations?cluster=TANGKAHAN" class="hover:text-white transition">{{ t('footer.region_tangkahan') }}</Link></li>
            <li><Link href="/destinations?cluster=BERASTAGI" class="hover:text-white transition">{{ t('footer.region_berastagi') }}</Link></li>
            <li><Link href="/destinations?cluster=MEDAN" class="hover:text-white transition">{{ t('footer.region_medan') }}</Link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-sm font-bold uppercase tracking-wider text-accent mb-3">{{ t('footer.services_title') }}</h4>
          <ul class="space-y-2 text-sm text-slate-300">
            <li><Link href="/tours" class="hover:text-white transition">{{ t('footer.service_tours') }}</Link></li>
            <li><Link href="/vehicles" class="hover:text-white transition">{{ t('footer.service_vehicles') }}</Link></li>
            <li><Link href="/custom-trip" class="hover:text-white transition">{{ t('footer.service_custom') }}</Link></li>
            <li><Link :href="route('partner.register')" class="hover:text-white transition text-accent font-semibold">{{ t('footer.join_partner') }}</Link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-sm font-bold uppercase tracking-wider text-accent mb-3">{{ t('footer.help_title') }}</h4>
          <p class="text-sm text-slate-300 leading-relaxed mb-3">
            {{ t('footer.help_desc') }}
          </p>
          <a
            href="https://wa.me/6281234567890"
            target="_blank"
            class="inline-flex items-center gap-2 bg-secondary hover:bg-secondary/90 text-white text-xs font-semibold py-2 px-3.5 rounded-lg transition"
          >
            <span>{{ t('footer.wa_consult') }}</span>
          </a>
        </div>
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-6 border-t border-white/10 text-center text-xs text-slate-400">
        {{ t('footer.copyright') }}
      </div>
    </footer>
  </div>
</template>

