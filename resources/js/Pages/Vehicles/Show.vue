<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
  product: Object,
});

const rentalDays = ref(1);

const decrementDays = () => {
  if (rentalDays.value > 1) {
    rentalDays.value--;
  }
};

const incrementDays = () => {
  rentalDays.value++;
};

const totalPrice = computed(() => {
  return props.product.base_price * rentalDays.value;
});

const dpAmount = computed(() => {
  return totalPrice.value * (props.product.dp_percentage / 100);
});

const remainingAmount = computed(() => {
  return totalPrice.value - dpAmount.value;
});

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
    <Head :title="`${product.name} — Sewa Kendaraan Danau Toba`" />

    <!-- Breadcrumb -->
    <div class="bg-surface-muted border-b border-border py-4 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex items-center gap-2 text-xs text-text-muted">
        <Link href="/" class="hover:text-primary">Beranda</Link>
        <span>/</span>
        <Link href="/vehicles" class="hover:text-primary">Rental Kendaraan</Link>
        <span>/</span>
        <span class="text-text-main font-semibold truncate">{{ product.name }}</span>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Main Photo -->
          <div class="rounded-2xl overflow-hidden shadow-card border border-border h-80 sm:h-96 bg-slate-100">
            <img
              :src="product.media[0]?.url || 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80'"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Title & Badges -->
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <span class="bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full">
                {{ product.vehicle.brand }} {{ product.vehicle.model }} ({{ product.vehicle.manufacturing_year }})
              </span>
              <span class="bg-secondary/10 text-secondary text-xs font-bold px-3 py-1 rounded-full">
                {{ product.vehicle.with_driver ? 'Termasuk Driver Berpengalaman' : 'Lepas Kunci' }}
              </span>
              <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200">
                Plat: {{ product.vehicle.license_plate }}
              </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-text-main">
              {{ product.name }}
            </h1>
            <p class="text-xs text-text-muted mt-2">
              Disediakan oleh mitra transportasi terdaftar: <span class="font-bold text-text-main">{{ product.partner.name }}</span> ({{ product.partner.city }})
            </p>
          </div>

          <!-- Vehicle Specifications Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-4 bg-surface rounded-2xl border border-border text-center">
              <span class="text-2xl block mb-1">👥</span>
              <span class="text-[11px] text-text-muted block">Kapasitas</span>
              <strong class="text-sm text-text-main">{{ product.vehicle.passenger_capacity }} Penumpang</strong>
            </div>

            <div class="p-4 bg-surface rounded-2xl border border-border text-center">
              <span class="text-2xl block mb-1">🧳</span>
              <span class="text-[11px] text-text-muted block">Bagasi</span>
              <strong class="text-sm text-text-main">{{ product.vehicle.luggage_capacity }} Koper</strong>
            </div>

            <div class="p-4 bg-surface rounded-2xl border border-border text-center">
              <span class="text-2xl block mb-1">⚙️</span>
              <span class="text-[11px] text-text-muted block">Transmisi</span>
              <strong class="text-sm text-text-main">{{ product.vehicle.transmission }}</strong>
            </div>

            <div class="p-4 bg-surface rounded-2xl border border-border text-center">
              <span class="text-2xl block mb-1">📅</span>
              <span class="text-[11px] text-text-muted block">Tahun Armada</span>
              <strong class="text-sm text-text-main">{{ product.vehicle.manufacturing_year }}</strong>
            </div>
          </div>

          <!-- Description -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card">
            <h2 class="text-base font-bold text-text-main mb-3">Informasi & Kondisi Kendaraan</h2>
            <p class="text-sm text-text-muted leading-relaxed whitespace-pre-line">
              {{ product.full_description }}
            </p>
          </div>

          <!-- Policy & Pickup locations -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
            <h3 class="text-base font-bold text-text-main">Titik Penjemputan & Kebijakan Rental</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div class="p-3.5 bg-surface-muted rounded-xl border border-border">
                <span class="font-bold text-text-main block mb-1">📍 Titik Pickup Fleksibel:</span>
                <ul class="text-text-muted list-disc list-inside space-y-0.5">
                  <li>Bandara Internasional Silangit (DTB)</li>
                  <li>Bandara Internasional Kualanamu (KNO) / Kota Medan</li>
                  <li>Pelabuhan Ajibata / Parapat / Tuktuk Samosir</li>
                </ul>
              </div>

              <div class="p-3.5 bg-surface-muted rounded-xl border border-border">
                <span class="font-bold text-text-main block mb-1">🛡️ Perlindungan Pemesanan:</span>
                <ul class="text-text-muted list-disc list-inside space-y-0.5">
                  <li>Down payment aman 30% via rekening platform</li>
                  <li>Hold unit selama 24 jam setelah mitra menyetujui</li>
                  <li>Refund 100% jika pembatalan > 7 hari sebelum jadwal</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky Booking Card (1 Col) -->
        <div>
          <div class="sticky top-20 bg-surface p-6 rounded-2xl border border-border shadow-xl space-y-5">
            <div>
              <span class="text-xs text-text-muted uppercase font-bold block">Tarif Sewa</span>
              <div class="text-2xl font-black text-primary">
                {{ formatRupiah(product.base_price) }}
                <span class="text-xs font-normal text-text-muted">/ hari</span>
              </div>
            </div>

            <!-- Rental Days Counter -->
            <div>
              <label class="block text-xs font-bold text-text-muted uppercase mb-1.5">Durasi Sewa (Hari)</label>
              <div class="flex items-center gap-3">
                <button
                  @click="decrementDays"
                  class="w-10 h-10 rounded-xl border border-border hover:bg-surface-muted font-bold text-lg flex items-center justify-center"
                >
                  -
                </button>
                <span class="flex-1 text-center font-bold text-base text-text-main">{{ rentalDays }} Hari</span>
                <button
                  @click="incrementDays"
                  class="w-10 h-10 rounded-xl border border-border hover:bg-surface-muted font-bold text-lg flex items-center justify-center"
                >
                  +
                </button>
              </div>
            </div>

            <!-- Price Breakdown -->
            <div class="p-4 bg-surface-muted rounded-xl border border-border space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-text-muted">Total Biaya Sewa ({{ rentalDays }} Hari):</span>
                <span class="font-bold text-text-main">{{ formatRupiah(totalPrice) }}</span>
              </div>
              <div class="flex justify-between text-emerald-700 font-bold pt-2 border-t border-border">
                <span>Wajib DP ({{ product.dp_percentage }}%):</span>
                <span>{{ formatRupiah(dpAmount) }}</span>
              </div>
              <div class="flex justify-between text-text-muted text-[11px]">
                <span>Pelunasan Hari Pertama:</span>
                <span>{{ formatRupiah(remainingAmount) }}</span>
              </div>
            </div>

            <!-- SLA & Lead time notice -->
            <div class="text-[11px] text-text-muted space-y-1">
              <div class="flex items-center gap-1.5">
                <span>⏱️</span>
                <span>Minimum lead-time: <strong class="text-text-main">H-12 Jam sebelum jadwal</strong></span>
              </div>
              <div class="flex items-center gap-1.5">
                <span>⚡</span>
                <span>Respon ketersediaan: <strong class="text-text-main">SLA 24 Jam mitra</strong></span>
              </div>
            </div>

            <!-- CTA Button -->
            <Link
              :href="`/checkout?product_id=${product.id}&rental_days=${rentalDays}`"
              class="w-full bg-accent hover:bg-accent-hover text-white font-bold py-3.5 px-4 rounded-xl text-center text-sm transition-colors shadow-lg block"
            >
              Ajukan Sewa Kendaraan →
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
