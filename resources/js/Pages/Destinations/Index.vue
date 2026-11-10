<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { t } = useTrans();

const props = defineProps({
  destinations: Object,
  clusters: Array,
  categories: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const selectedCluster = ref(props.filters.cluster || '');
const selectedCategory = ref(props.filters.category || '');

const applyFilters = () => {
  router.get('/destinations', {
    search: search.value || undefined,
    cluster: selectedCluster.value || undefined,
    category: selectedCategory.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const setCluster = (clusterVal) => {
  selectedCluster.value = selectedCluster.value === clusterVal ? '' : clusterVal;
  applyFilters();
};

</script>

<template>
  <AppLayout>
    <Head :title="t('destinations.title')" />

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-primary-dark via-primary to-primary-light text-white py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <span class="text-xs font-bold text-accent uppercase tracking-widest">{{ t('destinations.eyebrow') }}</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold mt-1">{{ t('destinations.title') }}</h1>
        <p class="text-sm text-slate-200 mt-2 max-w-2xl">
          {{ t('destinations.subtitle') }}
        </p>

        <!-- Search Bar -->
        <div class="mt-6 max-w-xl flex gap-2">
          <input
            v-model="search"
            @keyup.enter="applyFilters"
            type="text"
            :placeholder="t('destinations.search_placeholder')"
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
      <!-- Cluster Filter Tabs -->
      <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 scrollbar-none">
        <button
          @click="setCluster('')"
          :class="!selectedCluster ? 'bg-primary text-white shadow-md' : 'bg-surface text-text-muted hover:bg-surface-muted border border-border'"
          class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all"
        >
          {{ t('destinations.all_clusters', { total: destinations.total }) }}
        </button>
        <button
          v-for="c in clusters"
          :key="c.value"
          @click="setCluster(c.value)"
          :class="selectedCluster === c.value ? 'bg-primary text-white shadow-md' : 'bg-surface text-text-muted hover:bg-surface-muted border border-border'"
          class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all"
        >
          {{ c.label }}
        </button>
      </div>

      <!-- Destinations Grid -->
      <div v-if="destinations.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link
          v-for="dest in destinations.data"
          :key="dest.id"
          :href="`/destinations/${dest.slug}`"
          class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover hover:border-primary/40 transition-all flex flex-col group cursor-pointer"
        >
          <div class="relative h-48 overflow-hidden bg-slate-100">
            <img :src="dest.image_url" :alt="dest.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-primary shadow-sm">
              {{ dest.cluster_label }}
            </div>
          </div>

          <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
              <span class="text-[11px] font-semibold text-secondary uppercase">{{ dest.category || t('home.default_category') }}</span>
              <h3 class="text-base font-bold text-text-main mt-1 group-hover:text-primary transition-colors">
                {{ dest.name }}
              </h3>
              <p class="text-xs text-text-muted mt-2 line-clamp-2 leading-relaxed">
                {{ dest.description }}
              </p>
            </div>

            <div class="mt-4 pt-4 border-t border-border flex items-center justify-between text-xs">
              <div class="text-text-muted">
                <span class="block text-[10px] uppercase">{{ t('destinations.domestic_ticket') }}</span>
                <span class="font-bold text-primary text-sm">
                  {{ dest.ticket_price_domestic > 0 ? formatRupiah(dest.ticket_price_domestic) : t('home.free') }}
                </span>
              </div>
              <div class="text-right text-text-muted">
                <span class="block text-[10px] uppercase">{{ t('destinations.operating_hours') }}</span>
                <span class="font-medium text-text-main text-xs">{{ dest.operating_hours || '24 Jam' }}</span>
              </div>
            </div>
          </div>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-surface rounded-2xl border border-border">
        <span class="text-4xl">🔍</span>
        <h3 class="text-lg font-bold text-text-main mt-3">{{ t('destinations.empty_title') }}</h3>
        <p class="text-xs text-text-muted mt-1">{{ t('destinations.empty_desc') }}</p>
        <button
          @click="selectedCluster = ''; search = ''; applyFilters()"
          class="mt-4 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl"
        >
          {{ t('vehicles.reset_btn') }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>

