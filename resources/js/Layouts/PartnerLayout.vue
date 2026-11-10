<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useTrans } from '@/Composables/useTrans';
import { getInitials } from '@/Composables/useFormat';

const page = usePage();
const user = page.props.auth ? page.props.auth.user : null;
const isSidebarOpen = ref(false);
const isProfileOpen = ref(false);
const profileDropdownRef = ref(null);
const { t } = useTrans();

const closeDropdownOnOutside = (e) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
    isProfileOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdownOnOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdownOnOutside);
});
</script>

<template>
  <div class="min-h-screen bg-background text-text-main flex flex-col font-sans">
    <!-- Top Header Bar -->
    <header class="bg-surface border-b border-border sticky top-0 z-40">
      <div class="px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
        <!-- Header Left: Logo + Portal Title -->
        <div class="flex items-center gap-4">
          <button
            @click="isSidebarOpen = !isSidebarOpen"
            class="md:hidden p-2 rounded-lg text-text-muted hover:bg-surface-muted cursor-pointer"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="flex items-center gap-3">
            <ApplicationLogo className="h-8 w-auto" />
            <span class="text-sm font-bold text-text-main tracking-tight">
              {{ t('partner.portal_title') }}
            </span>
          </div>
        </div>

        <!-- Header Right: Language, Marketplace Link, & Clean Profile Dropdown -->
        <div class="flex items-center gap-3 sm:gap-4">
          <LanguageSwitcher />

          <Link
            href="/"
            class="text-xs font-semibold text-text-muted hover:text-primary transition hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl hover:bg-surface-muted"
          >
            <span>←</span>
            <span>{{ t('nav.back_to_marketplace') }}</span>
          </Link>

          <!-- Profile Dropdown Button & Menu -->
          <div class="relative" ref="profileDropdownRef">
            <button
              type="button"
              @click="isProfileOpen = !isProfileOpen"
              class="flex items-center gap-2.5 p-1.5 sm:px-3 sm:py-1.5 rounded-2xl hover:bg-surface-muted border border-transparent hover:border-border transition cursor-pointer select-none"
            >
              <!-- Avatar Circle -->
              <div class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 text-primary font-black text-xs flex items-center justify-center uppercase shrink-0">
                {{ getInitials(user?.name, 2, 'MV') }}
              </div>

              <!-- Name & Badge (Hidden on mobile) -->
              <div class="hidden sm:block text-left leading-tight">
                <div class="text-xs font-extrabold text-text-main line-clamp-1 max-w-[140px]">
                  {{ user?.name || 'Mitra' }}
                </div>
                <div class="text-[10px] text-emerald-700 font-semibold flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                  <span>{{ t('partner.status_verified') }}</span>
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

            <!-- Dropdown Menu Panel -->
            <div
              v-if="isProfileOpen"
              class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-2 z-50 animate-in fade-in zoom-in-95 duration-150"
            >
              <!-- User Info Card in Dropdown -->
              <div class="px-4 py-3 border-b border-border flex flex-col gap-1.5">
                <div>
                  <div class="text-xs font-black text-text-main break-words">{{ user?.name || 'Mitra Pengelola' }}</div>
                  <div class="text-[11px] text-text-muted break-all mt-0.5">{{ user?.email || 'mitra@mutermuter.id' }}</div>
                </div>
                <div class="inline-flex self-start items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200/70 text-[10px] font-bold text-emerald-800">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                  <span>Status: {{ t('partner.status_verified') }}</span>
                </div>
              </div>

              <!-- Navigation Menu Items -->
              <div class="p-1 space-y-0.5 text-xs">
                <Link
                  href="/partner"
                  @click="isProfileOpen = false"
                  class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                >
                  <span class="text-sm">📊</span>
                  <span>{{ t('partner.menu_summary') }}</span>
                </Link>

                <Link
                  href="/partner/staff"
                  @click="isProfileOpen = false"
                  class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                >
                  <span class="text-sm">👥</span>
                  <span>{{ t('partner.menu_staff') }}</span>
                </Link>

                <Link
                  href="/partner/settlements"
                  @click="isProfileOpen = false"
                  class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                >
                  <span class="text-sm">💰</span>
                  <span>{{ t('partner.menu_settlements') }}</span>
                </Link>

                <Link
                  href="/profile"
                  @click="isProfileOpen = false"
                  class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                >
                  <span class="text-sm">⚙️</span>
                  <span>Pengaturan Profil Akun</span>
                </Link>
              </div>

              <!-- Logout Button Row -->
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
        </div>
      </div>
    </header>

    <div class="flex-1 flex">
      <!-- Left Sidebar -->
      <aside
        :class="[
          'bg-surface border-r border-border w-64 p-4 space-y-1 shrink-0 transition-transform duration-200 z-30',
          'fixed inset-y-0 left-0 top-16 md:sticky md:top-16 md:h-[calc(100vh-4rem)] md:overflow-y-auto md:translate-x-0',
          isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]"
      >
        <!-- Mitra Vendor Badge Relocated to Sidebar Top (Stacked vertically so text never clips) -->
        <div class="px-3.5 py-2.5 mb-3 bg-emerald-50/80 border border-emerald-200/70 rounded-2xl flex flex-col gap-1.5">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span class="text-xs font-black text-emerald-950 uppercase tracking-wider">Mitra Vendor</span>
          </div>
          <div class="flex items-center">
            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-600 text-white">
              Status: {{ t('partner.status_verified') }}
            </span>
          </div>
        </div>

        <div class="text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          {{ t('partner.section_ops') }}
        </div>
        <Link
          href="/partner"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>📊</span>
          <span>{{ t('partner.menu_summary') }}</span>
        </Link>
        <Link
          href="/partner/bookings"
          class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <div class="flex items-center gap-3">
            <span>📥</span>
            <span>{{ t('partner.menu_bookings') }}</span>
          </div>
          <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-bold">24h SLA</span>
        </Link>
        <Link
          href="/partner/schedule"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🗓️</span>
          <span>{{ t('partner.menu_schedule') }}</span>
        </Link>

        <div class="pt-4 text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          {{ t('partner.section_fleet') !== 'partner.section_fleet' ? t('partner.section_fleet') : 'Armada & Transportasi' }}
        </div>
        <Link
          href="/partner/products"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🚐</span>
          <span>{{ t('partner.menu_fleet_list') !== 'partner.menu_fleet_list' ? t('partner.menu_fleet_list') : 'Daftar Unit Armada' }}</span>
        </Link>
        <Link
          href="/partner/vehicles/create"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>➕</span>
          <span>{{ t('partner.menu_add_vehicle') !== 'partner.menu_add_vehicle' ? t('partner.menu_add_vehicle') : 'Tambah Unit Armada' }}</span>
        </Link>

        <div class="pt-4 text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          {{ t('partner.section_business') }}
        </div>
        <Link
          href="/partner/staff"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>👥</span>
          <span>{{ t('partner.menu_staff') }}</span>
        </Link>
        <Link
          href="/partner/settlements"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>💰</span>
          <span>{{ t('partner.menu_settlements') }}</span>
        </Link>
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 p-6 md:p-8 max-w-7xl">
        <slot />
      </main>
    </div>
  </div>
</template>

