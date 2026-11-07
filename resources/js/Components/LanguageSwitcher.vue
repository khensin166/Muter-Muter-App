<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import FlagIcon from '@/Components/FlagIcon.vue';

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
  <div class="inline-flex items-center p-0.5 rounded-full bg-slate-100 border border-slate-200 shadow-2xs select-none">
    <!-- Indonesia -->
    <button
      type="button"
      @click="toggleLocale('id')"
      :class="[
        currentLocale === 'id'
          ? 'bg-white text-slate-900 font-bold shadow-xs'
          : 'text-slate-500 hover:text-slate-900 font-medium',
        'px-2.5 py-1 rounded-full text-xs flex items-center gap-1.5 transition-all duration-150 cursor-pointer'
      ]"
      title="Bahasa Indonesia"
    >
      <FlagIcon country="id" className="w-4 h-2.5" />
      <span class="text-[11px] font-bold tracking-wide">ID</span>
    </button>

    <!-- English -->
    <button
      type="button"
      @click="toggleLocale('en')"
      :class="[
        currentLocale === 'en'
          ? 'bg-white text-slate-900 font-bold shadow-xs'
          : 'text-slate-500 hover:text-slate-900 font-medium',
        'px-2.5 py-1 rounded-full text-xs flex items-center gap-1.5 transition-all duration-150 cursor-pointer'
      ]"
      title="English"
    >
      <FlagIcon country="en" className="w-4 h-2.5" />
      <span class="text-[11px] font-bold tracking-wide">EN</span>
    </button>
  </div>
</template>
