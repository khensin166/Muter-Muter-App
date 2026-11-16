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

const currentPath = computed(() => (page.url || '/').split('?')[0]);

const isNavActive = (prefix) => {
  if (prefix === '/') return currentPath.value === '/';
  if (prefix === '/dashboard') {
    return (
      currentPath.value.startsWith('/dashboard') ||
      currentPath.value.startsWith('/my-bookings') ||
      currentPath.value.startsWith('/profile')
    );
  }
  return currentPath.value.startsWith(prefix);
};

const navItems = computed(() => {
  const items = [
    { label: t('nav.home'), href: '/', prefix: '/' },
    { label: t('nav.explore_destinations'), href: '/destinations', prefix: '/destinations' },
    { label: t('nav.tour_packages'), href: '/tours', prefix: '/tours' },
    { label: t('nav.vehicle_rentals'), href: '/vehicles', prefix: '/vehicles' },
  ];

  if (user.value) {
    items.push({ label: t('nav.dashboard'), href: '/dashboard', prefix: '/dashboard' });
  }

  return items;
});

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

const handleKeyDown = (e) => {
  if (e.key === 'Escape') {
    isProfileOpen.value = false;
    showingNavigationDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleKeyDown);
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
            <nav class="hidden md:flex items-center space-x-1 text-sm" aria-label="Main Navigation">
              <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                :aria-current="isNavActive(item.prefix) ? 'page' : undefined"
                class="px-3 py-1.5 rounded-xl transition-colors duration-150 tactile-press whitespace-nowrap"
                :class="
                  isNavActive(item.prefix)
                    ? 'bg-primary/10 text-primary font-bold'
                    : 'text-text-main hover:text-primary hover:bg-slate-100/70 font-medium'
                "
              >
                {{ item.label }}
              </Link>
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
                  :aria-expanded="isProfileOpen"
                  aria-haspopup="menu"
                  class="flex items-center gap-2.5 p-1.5 sm:px-3 sm:py-1.5 rounded-2xl hover:bg-surface-muted border border-transparent hover:border-border transition duration-150 cursor-pointer select-none tactile-press"
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
                    class="w-4 h-4 text-slate-400 transition-transform duration-200 ease-[cubic-bezier(0.23,1,0.32,1)]"
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
                <Transition
                  enter-active-class="transition duration-150 ease-[cubic-bezier(0.23,1,0.32,1)] origin-top-right"
                  enter-from-class="opacity-0 scale-95 -translate-y-1"
                  enter-to-class="opacity-100 scale-100 translate-y-0"
                  leave-active-class="transition duration-100 ease-[cubic-bezier(0.23,1,0.32,1)] origin-top-right"
                  leave-from-class="opacity-100 scale-100 translate-y-0"
                  leave-to-class="opacity-0 scale-95 -translate-y-1"
                >
                  <div
                    v-if="isProfileOpen"
                    role="menu"
                    class="absolute right-0 mt-2 w-64 rounded-2xl bg-surface shadow-xl border border-border py-2 z-50 text-xs"
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

                    <div class="p-1.5 space-y-0.5">
                      <Link
                        v-if="user.role === 'ADMIN' || user.role === 'SUPER_ADMIN'"
                        href="/admin"
                        @click="isProfileOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-primary bg-primary/8 hover:bg-primary/15 font-bold transition tactile-press"
                      >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        <span>{{ t('nav.admin_panel') }}</span>
                      </Link>

                      <Link
                        v-if="user.role === 'PARTNER'"
                        href="/partner"
                        @click="isProfileOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-emerald-800 bg-emerald-50/70 hover:bg-emerald-50 font-bold transition tactile-press"
                      >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        <span>{{ t('nav.partner_portal') }}</span>
                      </Link>

                      <Link
                        :href="route('dashboard')"
                        @click="isProfileOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl font-medium transition tactile-press"
                        :class="currentPath === '/dashboard' ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-primary'"
                      >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <span>{{ t('nav.dashboard') }}</span>
                      </Link>

                      <Link
                        :href="route('bookings.my')"
                        @click="isProfileOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl font-medium transition tactile-press"
                        :class="currentPath.startsWith('/my-bookings') ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-primary'"
                      >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                        </svg>
                        <span>{{ t('dashboard.my_bookings') }}</span>
                      </Link>

                      <Link
                        href="/profile"
                        @click="isProfileOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl font-medium transition tactile-press"
                        :class="currentPath.startsWith('/profile') ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-primary'"
                      >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span>Pengaturan Akun &amp; Profil</span>
                      </Link>
                    </div>

                    <div class="p-1.5 border-t border-slate-100 mt-1">
                      <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        @click="isProfileOpen = false"
                        class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition cursor-pointer tactile-press"
                      >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        <span>{{ t('nav.logout') }}</span>
                      </Link>
                    </div>
                  </div>
                </Transition>
              </div>
            </template>
            <template v-else>
              <Link
                :href="route('partner.register')"
                class="text-xs font-semibold text-text-muted hover:text-primary transition flex items-center gap-1 px-2.5 py-1.5 rounded-xl hover:bg-slate-50 border border-slate-200/60 hidden lg:inline-flex tactile-press"
              >
                <span>🚗</span>
                <span>Jadi Mitra</span>
              </Link>
              <Link
                :href="route('login')"
                class="text-sm font-medium text-text-main hover:text-primary transition tactile-press"
              >
                {{ t('nav.login') }}
              </Link>
              <Link
                :href="route('register')"
                class="bg-accent hover:bg-accent-hover text-white text-sm font-semibold py-2 px-4 rounded-xl shadow-sm transition tactile-press"
              >
                {{ t('nav.register') }}
              </Link>
            </template>
          </div>

          <!-- Mobile Hamburger -->
          <div class="flex items-center gap-2 md:hidden">
            <LanguageSwitcher />

            <button
              type="button"
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              :aria-expanded="showingNavigationDropdown"
              aria-label="Toggle navigation menu"
              class="p-2 rounded-lg text-text-muted hover:text-text-main hover:bg-surface-muted transition tactile-press"
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
      <Transition
        enter-active-class="transition duration-200 ease-[cubic-bezier(0.23,1,0.32,1)] origin-top"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in origin-top"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <div v-show="showingNavigationDropdown" class="md:hidden border-b border-border bg-surface px-4 pt-2 pb-4 space-y-1">
          <Link
            v-for="item in navItems"
            :key="item.href"
            :href="item.href"
            @click="showingNavigationDropdown = false"
            :aria-current="isNavActive(item.prefix) ? 'page' : undefined"
            class="block px-3 py-2 rounded-xl text-sm transition-colors tactile-press"
            :class="
              isNavActive(item.prefix)
                ? 'bg-primary/10 text-primary font-bold'
                : 'text-text-main hover:bg-slate-50 hover:text-primary font-medium'
            "
          >
            {{ item.label }}
          </Link>
          <div class="pt-3 mt-2 border-t border-border flex flex-col gap-1.5">
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
              <Link
                v-if="user.role === 'ADMIN' || user.role === 'SUPER_ADMIN'"
                href="/admin"
                @click="showingNavigationDropdown = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold text-primary bg-primary/8 hover:bg-primary/15 transition tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <span>{{ t('nav.admin_panel') }}</span>
              </Link>
              <Link
                v-if="user.role === 'PARTNER'"
                href="/partner"
                @click="showingNavigationDropdown = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold text-emerald-700 bg-emerald-50/70 hover:bg-emerald-50 transition tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <span>{{ t('nav.partner_portal') }}</span>
              </Link>
              <Link
                :href="route('dashboard')"
                @click="showingNavigationDropdown = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-text-main hover:bg-slate-50 hover:text-primary transition tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span>{{ t('nav.dashboard') }}</span>
              </Link>
              <Link
                :href="route('bookings.my')"
                @click="showingNavigationDropdown = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-text-main hover:bg-slate-50 hover:text-primary transition tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                </svg>
                <span>{{ t('dashboard.my_bookings') }}</span>
              </Link>
              <Link
                href="/profile"
                @click="showingNavigationDropdown = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-text-main hover:bg-slate-50 hover:text-primary transition tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span>Pengaturan Profil</span>
              </Link>
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                @click="showingNavigationDropdown = false"
                class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-danger font-bold hover:bg-rose-50 transition tactile-press"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span>{{ t('nav.logout') }}</span>
              </Link>
            </template>
            <template v-else>
              <Link :href="route('partner.register')" @click="showingNavigationDropdown = false" class="block px-3 py-2 rounded-xl text-xs font-semibold text-primary bg-primary/5 hover:bg-primary/10 tactile-press">🚗 Daftar Jadi Mitra Rental</Link>
              <Link :href="route('login')" @click="showingNavigationDropdown = false" class="block px-3 py-2 rounded-xl text-sm font-medium text-text-main hover:bg-slate-50 tactile-press">{{ t('nav.login') }}</Link>
              <Link :href="route('register')" @click="showingNavigationDropdown = false" class="block py-2.5 text-center bg-accent hover:bg-accent-hover text-white rounded-xl font-semibold text-sm tactile-press">{{ t('nav.register_now') }}</Link>
            </template>
          </div>
        </div>
      </Transition>
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
            <li><Link href="/tours" class="hover:text-white transition">{{ t('footer.service_custom') }}</Link></li>
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

