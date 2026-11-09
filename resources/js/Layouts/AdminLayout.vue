<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useTrans } from '@/Composables/useTrans';

const page = usePage();
const user = page.props.auth ? page.props.auth.user : null;
const isSidebarOpen = ref(false);
const { t } = useTrans();
</script>

<template>
  <div class="min-h-screen bg-background text-text-main flex flex-col font-sans">
    <!-- Top Header Bar -->
    <header class="bg-primary-dark text-white border-b border-primary sticky top-0 z-40">
      <div class="px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
        <div class="flex items-center gap-4">
          <button
            @click="isSidebarOpen = !isSidebarOpen"
            class="md:hidden p-2 rounded-lg text-slate-300 hover:bg-primary"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="flex items-center gap-3">
            <ApplicationLogo variant="white" className="h-7 w-auto" />
            <span class="text-xs font-black uppercase tracking-wider px-2 py-0.5 bg-accent text-white rounded">ADMIN</span>
            <span class="text-sm font-bold tracking-tight hidden sm:inline">{{ t('admin.console_title') }}</span>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <LanguageSwitcher />
          <Link href="/" class="text-xs font-medium text-slate-300 hover:text-white hidden sm:inline">
            {{ t('nav.back_to_marketplace') }}
          </Link>
          <div class="text-right">
            <div class="text-sm font-bold">{{ user?.name || 'Administrator' }}</div>
            <div class="text-xs text-accent">Super Operations</div>
          </div>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white/10 hover:bg-danger text-white transition"
          >
            {{ t('nav.logout') }}
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
          {{ t('admin.section_ops') }}
        </div>
        <Link
          href="/admin"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>📈</span>
          <span>{{ t('admin.menu_dashboard') }}</span>
        </Link>
        <Link
          href="/admin/sla-monitoring"
          class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <div class="flex items-center gap-3">
            <span>🚨</span>
            <span>{{ t('admin.menu_sla') }}</span>
          </div>
          <span class="bg-rose-100 text-danger text-xs px-2 py-0.5 rounded-full font-bold">Manual</span>
        </Link>

        <div class="pt-4 text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          {{ t('admin.section_verification') }}
        </div>
        <Link
          href="/admin/payments"
          class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <div class="flex items-center gap-3">
            <span>💳</span>
            <span>{{ t('admin.menu_payments') }}</span>
          </div>
          <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-bold">Review</span>
        </Link>
        <Link
          href="/admin/partners"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🏢</span>
          <span>{{ t('admin.menu_partners') }}</span>
        </Link>
        <Link
          href="/admin/products"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🏷️</span>
          <span>{{ t('admin.menu_products') }}</span>
        </Link>

        <div class="pt-4 text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          {{ t('admin.section_finance') }}
        </div>
        <Link
          href="/admin/settlements"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>💸</span>
          <span>{{ t('admin.menu_settlements') }}</span>
        </Link>
        <Link
          href="/admin/refunds"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>🔄</span>
          <span>{{ t('admin.menu_refunds') }}</span>
        </Link>

        <div class="pt-4 text-xs font-bold uppercase tracking-wider text-text-muted px-3 py-2">
          {{ t('admin.section_master') }}
        </div>
        <Link
          href="/admin/destinations"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium hover:bg-surface-muted text-text-main transition"
        >
          <span>📍</span>
          <span>{{ t('admin.menu_destinations') }}</span>
        </Link>
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 p-6 md:p-8 max-w-7xl">
        <slot />
      </main>
    </div>
  </div>
</template>

