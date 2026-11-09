<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { t, locale } = useTrans();

const props = defineProps({
  tours: Object,
  tourTypes: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.tour_type || '');

const applyFilters = () => {
  router.get('/tours', {
    search: search.value || undefined,
    tour_type: selectedType.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const setTourType = (typeVal) => {
  selectedType.value = selectedType.value === typeVal ? '' : typeVal;
  applyFilters();
};

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};
</script>

<template>
  <AppLayout>
    <Head :title="t('tours.title')" />

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-primary-dark via-primary to-primary-light text-white py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <span class="text-xs font-bold text-accent uppercase tracking-widest">{{ t('tours.eyebrow') }}</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold mt-1">{{ t('tours.title') }}</h1>
        <p class="text-sm text-slate-200 mt-2 max-w-2xl">
          {{ t('tours.subtitle') }}
        </p>

        <!-- Search Bar -->
        <div class="mt-6 max-w-xl flex gap-2">
          <input
            v-model="search"
            @keyup.enter="applyFilters"
            type="text"
            :placeholder="t('tours.search_placeholder')"
            class="flex-1 bg-white text-slate-800 text-sm font-medium px-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-accent shadow-lg"
          />
          <button
            @click="applyFilters"
            class="bg-accent hover:bg-accent-hover text-white font-bold px-6 py-3 rounded-xl shadow-lg transition-colors text-sm"
          >
            {{ t('tours.search_btn') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Tour Type Filters -->
      <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 scrollbar-none">
        <button
          @click="setTourType('')"
          :class="!selectedType ? 'bg-primary text-white shadow-md' : 'bg-surface text-text-muted hover:bg-surface-muted border border-border'"
          class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all"
        >
          {{ t('tours.all_types', { total: tours.total }) }}
        </button>
        <button
          v-for="tt in tourTypes"
          :key="tt.value"
          @click="setTourType(tt.value)"
          :class="selectedType === tt.value ? 'bg-primary text-white shadow-md' : 'bg-surface text-text-muted hover:bg-surface-muted border border-border'"
          class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all"
        >
          {{ tt.label }}
        </button>
      </div>

      <!-- Tours Grid -->
      <div v-if="tours.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="tour in tours.data"
          :key="tour.id"
          class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover hover:border-primary/30 transition-all flex flex-col"
        >
          <div class="relative h-48 overflow-hidden bg-slate-200">
            <img :src="tour.image_url" :alt="tour.name" class="w-full h-full object-cover" />
            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
              <span class="bg-primary/90 text-white text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur-sm">
                {{ tour.duration_days }}{{ locale === 'en' ? 'D' : 'H' }}{{ tour.duration_nights }}{{ locale === 'en' ? 'N' : 'M' }}
              </span>
              <span v-if="tour.includes_ferry" class="bg-secondary/90 text-white text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur-sm flex items-center gap-1">
                {{ t('home.ferry_included') }}
              </span>
            </div>
          </div>

          <div class="p-6 flex-1 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between text-xs text-text-muted mb-2">
                <span>{{ tour.tour_type }}</span>
                <span class="text-secondary font-semibold">{{ t('home.min_days_lead', { days: tour.min_lead_time_hours / 24 }) }}</span>
              </div>
              <h3 class="text-lg font-bold text-text-main leading-snug hover:text-primary transition-colors">
                <Link :href="`/tours/${tour.slug}`">{{ tour.name }}</Link>
              </h3>
              <p class="text-xs text-text-muted mt-2 line-clamp-2">
                {{ tour.short_description }}
              </p>
            </div>

            <div class="mt-6 pt-4 border-t border-border">
              <div class="flex items-baseline justify-between">
                <div>
                  <span class="text-[10px] uppercase font-bold text-text-muted">{{ t('home.starting_from') }}</span>
                  <div class="text-xl font-black text-primary">
                    {{ formatRupiah(tour.base_price) }}
                    <span class="text-xs font-normal text-text-muted">{{ t('home.per_person') }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-[10px] uppercase font-bold text-emerald-600 block">{{ t('home.dp_only', { dp: tour.dp_percentage }) }}</span>
                  <span class="text-xs font-bold text-slate-700">{{ formatRupiah(tour.base_price * (tour.dp_percentage / 100)) }}</span>
                </div>
              </div>

              <Link
                :href="`/tours/${tour.slug}`"
                class="mt-4 w-full bg-primary hover:bg-primary-dark text-white font-bold py-2.5 px-4 rounded-xl text-center text-sm transition-colors block"
              >
                {{ t('home.tour_detail_btn') }}
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-surface rounded-2xl border border-border">
        <span class="text-4xl">🎒</span>
        <h3 class="text-lg font-bold text-text-main mt-3">{{ t('tours.empty_title') }}</h3>
        <p class="text-xs text-text-muted mt-1">{{ t('tours.empty_desc') }}</p>
        <button
          @click="selectedType = ''; search = ''; applyFilters()"
          class="mt-4 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl"
        >
          {{ t('tours.reset_btn') }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>

