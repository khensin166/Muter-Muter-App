<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';

const props = defineProps({
  partner: Object,
  products: Array,
  counts: Object,
  currentFilter: String,
});

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};

const toggleStatus = (productId) => {
  if (confirm('Ubah status publikasi produk ini?')) {
    router.patch(route('partner.products.toggle', productId));
  }
};
</script>

<template>
  <PartnerLayout>
    <Head title="Manajemen Produk — Mitra Vendor" />

    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">Katalog Mitra</span>
          <h1 class="text-2xl font-black text-text-main">Manajemen Produk & Layanan</h1>
          <p class="text-xs text-text-muted mt-0.5">Kelola paket wisata dan unit armada kendaraan Anda.</p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('partner.tours.create')"
            class="px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5"
          >
            <span>➕</span>
            <span>Tambah Paket Tour</span>
          </Link>
          <Link
            :href="route('partner.vehicles.create')"
            class="px-4 py-2.5 bg-secondary hover:bg-secondary/90 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5"
          >
            <span>➕</span>
            <span>Tambah Rental Armada</span>
          </Link>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-2 border-b border-border pb-2 text-xs">
        <Link
          :href="route('partner.products.index', { type: 'ALL' })"
          :class="currentFilter === 'ALL' ? 'text-primary font-bold border-b-2 border-primary pb-2' : 'text-text-muted hover:text-text-main pb-2'"
        >
          Semua Produk ({{ counts.all }})
        </Link>
        <Link
          :href="route('partner.products.index', { type: 'TOUR' })"
          :class="currentFilter === 'TOUR' ? 'text-primary font-bold border-b-2 border-primary pb-2' : 'text-text-muted hover:text-text-main pb-2'"
          class="ml-4"
        >
          Paket Tour ({{ counts.tours }})
        </Link>
        <Link
          :href="route('partner.products.index', { type: 'VEHICLE' })"
          :class="currentFilter === 'VEHICLE' ? 'text-primary font-bold border-b-2 border-primary pb-2' : 'text-text-muted hover:text-text-main pb-2'"
          class="ml-4"
        >
          Armada Rental ({{ counts.vehicles }})
        </Link>
        <span v-if="counts.pending_approval > 0" class="ml-auto text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full font-bold">
          ⏳ {{ counts.pending_approval }} Menunggu Review Admin
        </span>
      </div>

      <!-- Product Cards Grid -->
      <div v-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="p in products"
          :key="p.id"
          class="bg-surface rounded-2xl border border-border overflow-hidden shadow-card flex flex-col justify-between"
        >
          <div>
            <div class="relative h-44 bg-slate-100 overflow-hidden">
              <img
                :src="p.media_url || (p.product_type === 'TOUR' ? 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80' : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80')"
                :alt="p.name"
                class="w-full h-full object-cover"
              />
              <div class="absolute top-3 left-3 flex gap-1.5">
                <span class="bg-primary/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-md backdrop-blur-sm uppercase">
                  {{ p.product_type }}
                </span>
                <span
                  :class="{
                    'bg-emerald-600': p.approval_status === 'APPROVED',
                    'bg-amber-600': p.approval_status === 'PENDING',
                    'bg-rose-600': p.approval_status === 'REJECTED',
                  }"
                  class="text-white text-[10px] font-bold px-2 py-0.5 rounded-md backdrop-blur-sm"
                >
                  {{ p.approval_status === 'APPROVED' ? 'Disetujui' : (p.approval_status === 'PENDING' ? 'Review Admin' : 'Ditolak') }}
                </span>
              </div>
            </div>

            <div class="p-5">
              <h3 class="font-bold text-text-main text-base line-clamp-1">{{ p.name }}</h3>

              <div class="mt-2 text-xs text-text-muted space-y-1">
                <div v-if="p.product_type === 'TOUR'">
                  <span>⏱️ Durasi: {{ p.details.duration_days }} Hari | {{ p.details.tour_type }}</span>
                  <span v-if="p.details.includes_ferry" class="block text-emerald-600 font-semibold">🚢 Tiket Ferry Termasuk</span>
                </div>
                <div v-else>
                  <span>🚐 {{ p.details.brand }} {{ p.details.model }} ({{ p.details.passenger_capacity }} Kursi)</span>
                  <span class="block text-slate-700 font-semibold">{{ p.details.with_driver ? 'Termasuk Pengemudi' : 'Lepas Kunci' }}</span>
                </div>
                <div class="pt-1 text-[11px]">
                  <span>Lead Time: Min. H-{{ p.min_lead_time_hours / (p.product_type === 'TOUR' ? 24 : 1) }} {{ p.product_type === 'TOUR' ? 'Hari' : 'Jam' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-5 pt-0">
            <div class="pt-3 border-t border-border flex items-center justify-between">
              <div>
                <span class="text-[10px] text-text-muted uppercase block font-bold">Harga</span>
                <span class="text-sm font-black text-primary">{{ formatRupiah(p.base_price) }}</span>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="toggleStatus(p.id)"
                  :class="p.status === 'PUBLISHED' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-500 bg-slate-100 border-slate-300'"
                  class="text-xs font-bold px-2.5 py-1.5 rounded-lg border transition"
                >
                  {{ p.status === 'PUBLISHED' ? '🟢 Aktif' : '⚪ Arsip' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-surface rounded-2xl border border-border">
        <span class="text-4xl">📦</span>
        <h3 class="text-lg font-bold text-text-main mt-3">Belum ada produk terdaftar</h3>
        <p class="text-xs text-text-muted mt-1">Daftarkan paket tour atau armada transportasi Anda untuk mulai menerima booking wisatawan.</p>
        <div class="mt-4 flex justify-center gap-3">
          <Link :href="route('partner.tours.create')" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl">
            Tambah Paket Tour
          </Link>
          <Link :href="route('partner.vehicles.create')" class="px-4 py-2 bg-secondary text-white text-xs font-bold rounded-xl">
            Tambah Armada Rental
          </Link>
        </div>
      </div>
    </div>
  </PartnerLayout>
</template>

