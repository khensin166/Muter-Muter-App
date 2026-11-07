<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'id');

const toggleLocale = (targetLocale) => {
  if (targetLocale === currentLocale.value) return;

  router.post('/locale', { locale: targetLocale }, {
    preserveScroll: true,
    preserveState: false,
  });
};
</script>

<template>
  <div class="inline-flex items-center bg-surface-muted/80 p-0.5 rounded-lg border border-border text-xs font-semibold select-none shadow-2xs">
    <button
      type="button"
      @click="toggleLocale('id')"
      :class="[
        currentLocale === 'id'
          ? 'bg-surface text-primary shadow-xs font-bold'
          : 'text-text-muted hover:text-text-main',
        'px-2 py-1 rounded-md transition-all flex items-center gap-1 cursor-pointer'
      ]"
      title="Bahasa Indonesia"
    >
      <span>🇮🇩</span>
      <span>ID</span>
    </button>
    <button
      type="button"
      @click="toggleLocale('en')"
      :class="[
        currentLocale === 'en'
          ? 'bg-surface text-primary shadow-xs font-bold'
          : 'text-text-muted hover:text-text-main',
        'px-2 py-1 rounded-md transition-all flex items-center gap-1 cursor-pointer'
      ]"
      title="English"
    >
      <span>🇬🇧</span>
      <span>EN</span>
    </button>
  </div>
</template>
