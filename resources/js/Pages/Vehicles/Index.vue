<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { t } = useTrans();

const props = defineProps({
  vehicles: Object,
  vehicleTypes: Array,
  transmissions: Array,
  filters: Object,
});

const selectedType = ref(props.filters.vehicle_type || '');
const selectedTransmission = ref(props.filters.transmission || '');
const selectedWithDriver = ref(props.filters.with_driver || '');

const applyFilters = () => {
  router.get('/vehicles', {
    vehicle_type: selectedType.value || undefined,
    transmission: selectedTransmission.value || undefined,
    with_driver: selectedWithDriver.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

</script>

<template>
  <AppLayout>
    <Head :title="t('vehicles.title')" />

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-primary-dark via-primary to-primary-light text-white py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <span class="text-xs font-bold text-accent uppercase tracking-widest">{{ t('vehicles.eyebrow') }}</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold mt-1">{{ t('vehicles.title') }}</h1>
        <p class="text-sm text-slate-200 mt-2 max-w-2xl">
          {{ t('vehicles.subtitle') }}
        </p>

        <!-- Filters Row -->
        <div class="mt-6 max-w-3xl bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-[11px] font-bold text-slate-200 uppercase mb-1">{{ t('vehicles.filter_type') }}</label>
            <select
              v-model="selectedType"
              @change="applyFilters"
              class="w-full text-xs font-medium bg-white text-slate-800 rounded-xl border-none focus:ring-primary"
            >
              <option value="">{{ t('vehicles.all_types') }}</option>
              <option v-for="vt in vehicleTypes" :key="vt.value" :value="vt.value">{{ vt.label }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-slate-200 uppercase mb-1">{{ t('vehicles.filter_transmission') }}</label>
            <select
              v-model="selectedTransmission"
              @change="applyFilters"
              class="w-full text-xs font-medium bg-white text-slate-800 rounded-xl border-none focus:ring-primary"
            >
              <option value="">{{ t('vehicles.all_transmissions') }}</option>
              <option v-for="tr in transmissions" :key="tr.value" :value="tr.value">{{ tr.label }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-slate-200 uppercase mb-1">{{ t('vehicles.filter_driver') }}</label>
            <select
              v-model="selectedWithDriver"
              @change="applyFilters"
              class="w-full text-xs font-medium bg-white text-slate-800 rounded-xl border-none focus:ring-primary"
            >
              <option value="">{{ t('vehicles.all_driver_options') }}</option>
              <option value="true">{{ t('vehicles.with_driver') }}</option>
              <option value="false">{{ t('home.self_drive') }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div v-if="vehicles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="veh in vehicles.data"
          :key="veh.id"
          class="bg-surface rounded-2xl overflow-hidden border border-border hover:shadow-card-hover transition-all flex flex-col"
        >
          <div class="relative h-48 overflow-hidden bg-slate-100">
            <img :src="veh.image_url" :alt="veh.name" class="w-full h-full object-cover" />
            <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-slate-800 shadow-sm">
              {{ t('home.min_hours_lead', { hours: veh.min_lead_time_hours }) }}
            </div>
          </div>

          <div class="p-6 flex-1 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between text-xs text-text-muted mb-1">
                <span>{{ veh.brand }} {{ veh.year }}</span>
                <span class="font-semibold text-emerald-600">{{ veh.with_driver ? t('home.with_driver') : t('home.self_drive') }}</span>
              </div>
              <h3 class="text-lg font-bold text-text-main">
                <Link :href="`/vehicles/${veh.slug}`">{{ veh.name }}</Link>
              </h3>

              <div class="mt-3 flex items-center gap-4 text-xs text-text-muted font-medium bg-surface-muted p-2.5 rounded-xl border border-border">
                <div class="flex items-center gap-1">
                  <span>👥</span>
                  <span>{{ veh.passenger_capacity }} {{ t('home.persons') }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <span>🧳</span>
                  <span>{{ veh.luggage_capacity }} {{ t('home.suitcases') }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <span>⚙️</span>
                  <span>{{ veh.transmission }}</span>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
              <div>
                <span class="text-[10px] uppercase font-bold text-text-muted">{{ t('home.rental_rate') }}</span>
                <div class="text-xl font-black text-primary">
                  {{ formatRupiah(veh.base_price) }}
                  <span class="text-xs font-normal text-text-muted">{{ t('home.per_day') }}</span>
                </div>
              </div>
              <Link
                :href="`/vehicles/${veh.slug}`"
                class="bg-accent hover:bg-accent-hover text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-colors shadow-sm"
              >
                {{ t('vehicles.view_detail_btn') }}
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-surface rounded-2xl border border-border">
        <span class="text-4xl">🚐</span>
        <h3 class="text-lg font-bold text-text-main mt-3">{{ t('vehicles.empty_title') }}</h3>
        <p class="text-xs text-text-muted mt-1">{{ t('vehicles.empty_desc') }}</p>
        <button
          @click="selectedType = ''; selectedTransmission = ''; selectedWithDriver = ''; applyFilters()"
          class="mt-4 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl"
        >
          {{ t('vehicles.reset_btn') }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>

