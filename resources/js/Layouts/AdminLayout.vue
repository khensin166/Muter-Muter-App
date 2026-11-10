<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useTrans } from '@/Composables/useTrans';
import { getInitials } from '@/Composables/useFormat';

const page = usePage();
const user = computed(() => (page.props.auth ? page.props.auth.user : null));
const currentUrl = computed(() => page.url || '');
const isSidebarOpen = ref(false);
const isProfileOpen = ref(false);
const profileDropdownRef = ref(null);
const { t } = useTrans();

const isActive = (path, exact = false) => {
  if (exact) {
    return currentUrl.value === path;
  }
  return currentUrl.value.startsWith(path);
};

const userInitials = computed(() => getInitials(user.value?.name, 2, 'AD'));

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
  <div class="min-h-screen bg-background text-text-main flex flex-col font-sans antialiased">
    <!-- Top Enterprise Header Bar -->
    <header class="bg-surface border-b border-border sticky top-0 z-40 shadow-2xs">
      <div class="px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
        <!-- Left: Brand & Console Title -->
        <div class="flex items-center gap-4">
          <button
            @click="isSidebarOpen = !isSidebarOpen"
            class="md:hidden p-2 rounded-xl text-text-muted hover:bg-surface-muted hover:text-text-main transition"
            aria-label="Toggle Menu"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <Link href="/admin" class="flex items-center gap-3 group">
            <ApplicationLogo className="h-7 w-auto" />
            <div class="h-5 w-px bg-border hidden sm:block"></div>
            <span class="text-sm font-bold text-text-muted hidden sm:inline">
              {{ t('admin.console_title') }}
            </span>
          </Link>
        </div>

        <!-- Right: Actions, Language & Profile -->
        <div class="flex items-center gap-3 sm:gap-4">
          <LanguageSwitcher />

          <Link
            href="/"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-text-muted hover:text-primary px-2.5 py-1.5 rounded-lg hover:bg-surface-muted transition hidden sm:inline-flex"
          >
            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            <span>{{ t('nav.back_to_marketplace') }}</span>
          </Link>

          <div class="h-5 w-px bg-border hidden sm:block"></div>

          <!-- Profile Dropdown Button & Menu -->
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

              <!-- Name & Badge (Hidden on mobile) -->
              <div class="hidden sm:block text-left leading-tight">
                <div class="text-xs font-bold text-text-main line-clamp-1 max-w-[140px]">
                  {{ user?.name || 'Administrator' }}
                </div>
                <div class="text-[10px] text-primary font-semibold flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                  <span>Super Operations</span>
                </div>
              </div>

              <!-- Chevron Icon -->
              <svg
                class="w-4 h-4 text-text-muted transition-transform duration-200"
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
                  <div class="font-bold text-text-main text-sm break-words">{{ user?.name || 'Administrator' }}</div>
                  <div class="text-text-muted text-[11px] break-all">{{ user?.email }}</div>
                </div>
                <div class="inline-flex self-start items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20">
                  <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                  <span>Status: Super Admin Console</span>
                </div>
              </div>

              <div class="p-1 space-y-0.5">
                <Link
                  href="/admin"
                  @click="isProfileOpen = false"
                  class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                >
                  <span class="text-sm">📊</span>
                  <span>{{ t('admin.menu_dashboard') }}</span>
                </Link>

                <Link
                  href="/"
                  @click="isProfileOpen = false"
                  class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                >
                  <span class="text-sm">🌐</span>
                  <span>{{ t('nav.back_to_marketplace') }}</span>
                </Link>

                <Link
                  href="/profile"
                  @click="isProfileOpen = false"
                  class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition"
                >
                  <span class="text-sm">⚙️</span>
                  <span>Pengaturan Akun Admin</span>
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
      <!-- Left Enterprise Sidebar -->
      <aside
        :class="[
          'bg-surface border-r border-border w-64 py-5 px-3.5 flex flex-col justify-between shrink-0 transition-transform duration-200 z-30',
          'fixed inset-y-0 left-0 top-16 md:sticky md:top-16 md:h-[calc(100vh-4rem)] md:overflow-y-auto md:translate-x-0',
          isSidebarOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full md:translate-x-0'
        ]"
      >
        <div class="space-y-6">
          <!-- Admin Console Badge Relocated to Sidebar Top (Stacked vertically so text never clips) -->
          <div class="px-3.5 py-2.5 bg-primary/5 border border-primary/20 rounded-2xl flex flex-col gap-1.5">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
              <span class="text-xs font-black text-text-main uppercase tracking-wider">Admin Console</span>
            </div>
            <div class="flex items-center">
              <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-primary text-white">
                Status: Super Operations
              </span>
            </div>
          </div>

          <!-- Section 1: Operations -->
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">
              {{ t('admin.section_ops') }}
            </div>
            <nav class="space-y-1">
              <Link
                href="/admin"
                :class="[
                  'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin', true)
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <svg class="w-4 h-4 shrink-0" :class="isActive('/admin', true) ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span>{{ t('admin.menu_dashboard') }}</span>
              </Link>

              <Link
                href="/admin/sla-monitoring"
                :class="[
                  'flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/sla-monitoring')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/sla-monitoring') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                  </svg>
                  <span>{{ t('admin.menu_sla') }}</span>
                </div>
                <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200/70 text-[10px] px-2 py-0.5 rounded-full font-semibold">
                  <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                  SLA 24h
                </span>
              </Link>
            </nav>
          </div>

          <!-- Section 2: Master Catalog & Curated Tours -->
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">
              Katalog & Paket Wisata
            </div>
            <nav class="space-y-1">
              <Link
                href="/admin/destinations"
                :class="[
                  'flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/destinations')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/destinations') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                  </svg>
                  <span>{{ t('admin.menu_destinations') }}</span>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">Master</span>
              </Link>

              <Link
                href="/admin/destination-clusters"
                :class="[
                  'flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/destination-clusters')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/destination-clusters') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                  </svg>
                  <span>Klaster Wilayah</span>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-primary/10 text-primary">CRUD</span>
              </Link>

              <Link
                href="/admin/tours/create"
                :class="[
                  'flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/tours/create')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/tours/create') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                  </svg>
                  <span>Buat Paket Wisata</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200/60">+ Baru</span>
              </Link>

              <Link
                href="/admin/products"
                :class="[
                  'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/products')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/products') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                <span>{{ t('admin.menu_products') }}</span>
              </Link>
            </nav>
          </div>

          <!-- Section 3: Verification & Partners -->
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">
              {{ t('admin.section_verification') }}
            </div>
            <nav class="space-y-1">
              <Link
                href="/admin/payments"
                :class="[
                  'flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/payments')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/payments') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                  </svg>
                  <span>{{ t('admin.menu_payments') }}</span>
                </div>
                <span class="bg-amber-50 text-amber-800 border border-amber-200/70 text-[10px] px-2 py-0.5 rounded-full font-semibold">
                  Review
                </span>
              </Link>

              <Link
                href="/admin/partners"
                :class="[
                  'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/partners')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/partners') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                <span>{{ t('admin.menu_partners') }}</span>
              </Link>
            </nav>
          </div>

          <!-- Section 4: Finance & Settlements -->
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">
              {{ t('admin.section_finance') }}
            </div>
            <nav class="space-y-1">
              <Link
                href="/admin/settlements"
                :class="[
                  'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/settlements')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/settlements') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
                <span>{{ t('admin.menu_settlements') }}</span>
              </Link>

              <Link
                href="/admin/refunds"
                :class="[
                  'flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all group',
                  isActive('/admin/refunds')
                    ? 'bg-primary/8 text-primary font-bold border-l-3 border-primary'
                    : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900'
                ]"
              >
                <svg class="w-4 h-4 shrink-0" :class="isActive('/admin/refunds') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-700'" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>{{ t('admin.menu_refunds') }}</span>
              </Link>
            </nav>
          </div>
        </div>

        <!-- Bottom Platform Guarantee Footer Note -->
        <div class="mt-8 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
          <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Muter-Muter Core v1.2</span>
          </div>
          <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
            Standar Operasional Sumatera Utara • SLA 24h & Garansi Armada Aktif.
          </p>
        </div>
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 p-5 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <slot />
      </main>
    </div>
  </div>
</template>
