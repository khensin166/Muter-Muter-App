<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useTrans } from '@/Composables/useTrans';
import { getInitials } from '@/Composables/useFormat';

const showingNavigationDropdown = ref(false);
const isProfileOpen = ref(false);
const profileDropdownRef = ref(null);

const page = usePage();
const user = computed(() => (page.props.auth ? page.props.auth.user : null));
const { t } = useTrans();

const userInitials = computed(() => getInitials(user.value?.name));

const userRoleLabel = computed(() => {
  if (!user.value) return '';
  if (user.value.role === 'ADMIN' || user.value.role === 'SUPER_ADMIN') return 'Administrator';
  if (user.value.role === 'PARTNER') return 'Mitra Rental';
  return 'Wisatawan';
});

const handleClickOutside = (e) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
    isProfileOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
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
              <!-- Unified Profile Dropdown Button & Popover -->
              <div class="relative" ref="profileDropdownRef">
                <button
                  type="button"
                  @click="isProfileOpen = !isProfileOpen"
                  class="flex items-center gap-2.5 p-1.5 sm:px-3 sm:py-1.5 rounded-2xl hover:bg-surface-muted border border-transparent hover:border-border transition cursor-pointer select-none"
                >
                  <!-- Avatar Circle -->
                  <div class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 text-primary font-black text-xs flex items-center justify-center uppercase shrink-0">
                    {{ userInitials }}
                  </div>

                  <!-- Name & Badge -->
                  <div class="hidden sm:block text-left leading-tight">
                    <div class="text-xs font-extrabold text-text-main line-clamp-1 max-w-[130px]">
                      {{ user.name }}
                    </div>
                    <div class="text-[10px] text-text-muted font-semibold flex items-center gap-1">
                      <span
                        class="w-1.5 h-1.5 rounded-full"
                        :class="user.role === 'PARTNER' ? 'bg-emerald-500' : 'bg-primary'"
                      ></span>
                      <span>{{ userRoleLabel }}</span>
                    </div>
                  </div>

                  <!-- Chevron Icon -->
                  <svg
                    class="w-4 h-4 text-slate-400 transition-transform duration-200"
                    :class="isProfileOpen ? 'rotate-180 text-primary' : ''"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                  </svg>
                </button>

                <!-- Popover Menu -->
                <div
                  v-if="isProfileOpen"
                  class="absolute right-0 mt-2 w-64 rounded-2xl bg-surface shadow-xl border border-border py-2 z-50 text-xs animate-in fade-in slide-in-from-top-1 duration-150"
                >
                  <div class="px-4 py-3 border-b border-border flex flex-col gap-1.5">
                    <div>
                      <div class="font-bold text-text-main text-sm break-words">{{ user.name }}</div>
                      <div class="text-text-muted text-[11px] break-all">{{ user.email }}</div>
                    </div>
                    <div
                      class="inline-flex self-start items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                      :class="user.role === 'PARTNER' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-primary/10 text-primary border border-primary/20'"
                    >
                      <span class="w-1.5 h-1.5 rounded-full" :class="user.role === 'PARTNER' ? 'bg-emerald-500' : 'bg-primary'"></span>
                      <span>Status: {{ userRoleLabel }}</span>
                    </div>
                  </div>

                  <div class="p-1 space-y-0.5">
                    <Link
                      v-if="user.role === 'ADMIN' || user.role === 'SUPER_ADMIN'"
                      href="/admin"
                      @click="isProfileOpen = false"
                      class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-primary bg-primary/8 hover:bg-primary/15 font-bold transition"
                    >
                      <span class="text-sm">🛡️</span>
                      <span>{{ t('nav.admin_panel') }}</span>
                    </Link>

                    <Link
                      v-if="user.role === 'PARTNER'"
                      href="/partner"
                      @click="isProfileOpen = false"
                      class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-emerald-800 bg-emerald-50/60 hover:bg-emerald-50 font-bold transition"
                    >
                      <span class="text-sm">🚐</span>
                      <span>{{ t('nav.partner_portal') }}</span>
                    </Link>

                    <Link
                      :href="route('dashboard')"
                      @click="isProfileOpen = false"
                      class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                    >
                      <span class="text-sm">📊</span>
                      <span>{{ t('nav.dashboard') }}</span>
                    </Link>

                    <Link
                      :href="route('bookings.my')"
                      @click="isProfileOpen = false"
                      class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                    >
                      <span class="text-sm">📋</span>
                      <span>{{ t('dashboard.my_bookings') }}</span>
                    </Link>

                    <Link
                      href="/profile"
                      @click="isProfileOpen = false"
                      class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                    >
                      <span class="text-sm">⚙️</span>
                      <span>Pengaturan Akun & Profil</span>
                    </Link>
                  </div>

                  <div class="p-1 border-t border-slate-100 mt-1">
                    <Link
                      :href="route('logout')"
                      method="post"
                      as="button"
                      @click="isProfileOpen = false"
                      class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                      </svg>
                      <span>{{ t('nav.logout') }}</span>
                    </Link>
                  </div>
                </div>
              </div>
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
            <div class="px-3 py-2 flex items-center gap-2.5 bg-slate-50 rounded-xl mb-1 border border-slate-100">
              <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary font-black text-xs flex items-center justify-center shrink-0">
                {{ userInitials }}
              </div>
              <div class="leading-tight min-w-0">
                <div class="font-bold text-xs text-text-main truncate">{{ user.name }}</div>
                <div class="text-[11px] text-text-muted truncate">{{ user.email }}</div>
              </div>
            </div>
            <Link v-if="user.role === 'ADMIN' || user.role === 'SUPER_ADMIN'" href="/admin" class="block py-2 text-sm font-bold text-purple-700">🛡️ {{ t('nav.admin_panel') }}</Link>
            <Link v-if="user.role === 'PARTNER'" href="/partner" class="block py-2 text-sm font-bold text-emerald-700">🚐 {{ t('nav.partner_portal') }}</Link>
            <Link :href="route('dashboard')" class="block py-2 text-sm font-medium text-text-main hover:text-primary">📊 {{ t('nav.dashboard') }}</Link>
            <Link :href="route('bookings.my')" class="block py-2 text-sm font-medium text-text-main hover:text-primary">📋 {{ t('dashboard.my_bookings') }}</Link>
            <Link href="/profile" class="block py-2 text-sm font-medium text-text-main hover:text-primary">⚙️ Pengaturan Profil</Link>
            <Link :href="route('logout')" method="post" as="button" class="text-left py-2 text-sm text-danger font-bold">🚪 {{ t('nav.logout') }}</Link>
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

