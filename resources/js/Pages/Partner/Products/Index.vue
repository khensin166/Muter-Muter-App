<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';

const props = defineProps({
  partner: Object,
  products: Array,
  counts: Object,
  currentFilter: String,
});


const toggleStatus = (productId) => {
  if (confirm('Ubah status publikasi armada ini?')) {
    router.patch(route('partner.products.toggle', productId));
  }
};
</script>

<template>
  <PartnerLayout>
    <Head title="Manajemen Armada Rental — Mitra Vendor" />

    <div class="p-6 max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">Armada Mitra</span>
          <h1 class="text-2xl font-black text-text-main">Manajemen Unit Armada Rental</h1>
          <p class="text-xs text-text-muted mt-0.5">Kelola unit kendaraan, nomor polisi, kapasitas penumpang, dan status supir Anda.</p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('partner.vehicles.create')"
            class="px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5"
          >
            <span>➕</span>
            <span>Daftarkan Unit Armada</span>
          </Link>
        </div>
      </div>

      <!-- Filter Tabs / Stats Row -->
      <div class="flex items-center justify-between border-b border-border pb-2 text-xs">
        <div class="flex items-center gap-2">
          <span class="text-primary font-bold border-b-2 border-primary pb-2 flex items-center gap-1.5">
            <span>🚐</span>
            <span>Semua Unit Armada ({{ counts.vehicles || counts.all || 0 }})</span>
          </span>
        </div>
        <span v-if="counts.pending_approval > 0" class="text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full font-bold text-[11px] flex items-center gap-1">
          <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
          <span>{{ counts.pending_approval }} Menunggu Review Admin</span>
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
                :src="p.media_url || 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80'"
                :alt="p.name"
                class="w-full h-full object-cover"
              />
              <div class="absolute top-3 left-3 flex gap-1.5">
                <span class="bg-primary/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-md backdrop-blur-sm uppercase">
                  Armada
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
                <div>
                  <span class="font-semibold text-slate-800">🚐 {{ p.details?.brand }} {{ p.details?.model }}</span>
                  <span class="ml-1 text-slate-500">({{ p.details?.passenger_capacity || 4 }} Kursi)</span>
                </div>
                <div>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium" :class="p.details?.with_driver ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/60' : 'bg-slate-100 text-slate-700'">
                    <span>{{ p.details?.with_driver ? '👨‍✈️ Dengan Pengemudi' : '🔑 Lepas Kunci' }}</span>
                  </span>
                </div>
                <div class="pt-1 text-[11px] text-slate-500">
                  <span>Lead Time Booking: Min. H-{{ p.min_lead_time_hours || 12 }} Jam</span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-5 pt-0">
            <div class="pt-3 border-t border-border flex items-center justify-between">
              <div>
                <span class="text-[10px] text-text-muted uppercase block font-bold">Tarif Sewa / Hari</span>
                <span class="text-sm font-black text-primary">{{ formatRupiah(p.base_price) }}</span>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="toggleStatus(p.id)"
                  :class="p.status === 'PUBLISHED' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-500 bg-slate-100 border-slate-300'"
                  class="text-xs font-bold px-2.5 py-1.5 rounded-lg border transition cursor-pointer"
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
        <span class="text-4xl">🚐</span>
        <h3 class="text-lg font-bold text-text-main mt-3">Belum ada armada terdaftar</h3>
        <p class="text-xs text-text-muted mt-1 max-w-md mx-auto">Daftarkan unit kendaraan rental Anda (Avanza, Innova, HiAce, dsb) untuk mulai menerima pesanan sewa dari wisatawan.</p>
        <div class="mt-4 flex justify-center gap-3">
          <Link :href="route('partner.vehicles.create')" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl shadow-xs hover:bg-primary-dark transition">
            ➕ Daftarkan Unit Armada
          </Link>
        </div>
      </div>
    </div>
  </PartnerLayout>
</template>
