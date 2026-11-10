<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTrans } from '@/Composables/useTrans';

const { t, locale } = useTrans();

const props = defineProps({
  bookings: Object,
});

</script>

<template>
  <AppLayout>
    <Head :title="t('my_bookings.title')" />

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">{{ t('my_bookings.eyebrow') }}</span>
          <h1 class="text-2xl font-black text-text-main">{{ t('my_bookings.heading') }}</h1>
          <p class="text-xs text-text-muted mt-0.5">{{ t('my_bookings.subtitle') }}</p>
        </div>

        <Link
          href="/tours"
          class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold hover:bg-primary-dark transition shadow-sm"
        >
          {{ t('my_bookings.new_trip_btn') }}
        </Link>
      </div>

      <!-- Bookings List -->
      <div v-if="bookings.data.length > 0" class="space-y-4">
        <div
          v-for="b in bookings.data"
          :key="b.id"
          class="p-5 bg-surface rounded-2xl border border-border shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div>
            <div class="flex items-center gap-2 mb-1.5">
              <span class="font-mono text-xs font-bold text-primary">{{ b.booking_code }}</span>
              <span
                :class="{
                  'bg-amber-100 text-amber-900': b.booking_status === 'PENDING_APPROVAL',
                  'bg-purple-100 text-purple-900': b.booking_status === 'MANUAL_REVIEW',
                  'bg-blue-100 text-blue-900': b.booking_status === 'AWAITING_PAYMENT',
                  'bg-emerald-100 text-emerald-900': b.booking_status === 'CONFIRMED',
                  'bg-rose-100 text-danger': ['REJECTED', 'CANCELLED', 'EXPIRED'].includes(b.booking_status),
                }"
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
              >
                {{ b.booking_status_label }}
              </span>
            </div>

            <h3 class="font-bold text-text-main text-base">{{ b.product_name }}</h3>
            <div class="mt-1 text-xs text-text-muted flex items-center gap-3">
              <span>📅 {{ t('dashboard.departure_date') }}: {{ b.service_start_date }}</span>
              <span>🏢 {{ b.partner_name }}</span>
            </div>
          </div>

          <div class="flex sm:flex-col items-center sm:items-end justify-between border-t sm:border-t-0 pt-3 sm:pt-0 border-border gap-2">
            <div>
              <span class="text-[10px] uppercase text-text-muted block text-right font-bold">{{ t('dashboard.total_amount') }}</span>
              <span class="text-base font-black text-primary">{{ formatRupiah(b.total_price) }}</span>
            </div>

            <Link
              :href="route('bookings.show', b.booking_code)"
              class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold transition shadow-xs"
            >
              {{ t('dashboard.view_detail') }} →
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-surface rounded-2xl border border-border">
        <span class="text-4xl">🎒</span>
        <h3 class="text-lg font-bold text-text-main mt-3">{{ t('my_bookings.empty_title') }}</h3>
        <p class="text-xs text-text-muted mt-1">{{ t('my_bookings.empty_desc') }}</p>
        <div class="mt-4 flex justify-center gap-3">
          <Link href="/tours" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl">
            {{ t('dashboard.find_tours') }}
          </Link>
          <Link href="/vehicles" class="px-4 py-2 bg-secondary text-white text-xs font-bold rounded-xl">
            {{ t('dashboard.rent_vehicle') }}
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

