<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  products: Object,
  counts: Object,
  filters: Object,
});

const statusTabs = [
  { key: 'PENDING_REVIEW', label: 'Menunggu Review', count: props.counts.pending, color: 'text-amber-700 bg-amber-50' },
  { key: 'APPROVED', label: 'Disetujui', count: props.counts.approved, color: 'text-emerald-700 bg-emerald-50' },
  { key: 'REJECTED', label: 'Ditolak', count: props.counts.rejected, color: 'text-danger bg-rose-50' },
  { key: 'ALL', label: 'Semua Produk', count: props.counts.all, color: '' },
];

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};
</script>

<template>
  <AdminLayout>
    <Head title="Moderasi Produk Katalog — Admin Console" />

    <!-- Header Section -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-text-main">Moderasi Produk & Katalog Marketplace</h1>
        <p class="text-xs text-text-muted mt-0.5">
          Verifikasi kelayakan paket tour dan kendaraan sewaan mitra lokal sebelum dipublikasikan ke wisatawan.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-purple-50 text-purple-800 border border-purple-200">
          Katalog Quality Control
        </span>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
      <Link
        v-for="tab in statusTabs"
        :key="tab.key"
        :href="route('admin.products.index', { status: tab.key })"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0',
          filters.status === tab.key
            ? 'bg-primary text-white shadow-xs'
            : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
        ]"
      >
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.count !== null"
          :class="[
            'px-2 py-0.5 rounded-full text-[10px] font-black',
            filters.status === tab.key ? 'bg-white/20 text-white' : tab.color
          ]"
        >
          {{ tab.count }}
        </span>
      </Link>
    </div>

    <!-- Products Table -->
    <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
      <div class="p-6 border-b border-border flex items-center justify-between">
        <h2 class="text-base font-bold text-text-main">
          Daftar Produk ({{ products.total }})
        </h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-border bg-surface-muted/50 text-[11px] font-bold text-text-muted uppercase tracking-wider">
              <th class="py-3 px-6">Produk & Layanan</th>
              <th class="py-3 px-6">Mitra Penyedia</th>
              <th class="py-3 px-6">Tarif Dasar</th>
              <th class="py-3 px-6">Status Moderasi</th>
              <th class="py-3 px-6">Tgl Pengajuan</th>
              <th class="py-3 px-6 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="p in products.data" :key="p.id" class="hover:bg-surface-muted/30 transition">
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <img
                    :src="p.media_url || (p.product_type === 'TOUR' ? 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=100&q=80' : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=100&q=80')"
                    class="w-12 h-12 rounded-xl object-cover shrink-0"
                  />
                  <div>
                    <span class="font-bold text-text-main text-sm block">{{ p.name }}</span>
                    <span class="text-[11px] text-text-muted">{{ p.summary }}</span>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <span class="font-bold text-text-main block">{{ p.partner_name }}</span>
                <span class="text-text-muted text-[11px]">{{ p.partner_city }}</span>
              </td>
              <td class="py-4 px-6">
                <span class="font-black text-primary text-sm">{{ formatRupiah(p.base_price) }}</span>
                <span class="text-[10px] text-text-muted block uppercase">{{ p.pricing_type }}</span>
              </td>
              <td class="py-4 px-6">
                <span
                  :class="{
                    'bg-emerald-50 text-emerald-700 border-emerald-200': p.approval_status === 'APPROVED',
                    'bg-amber-50 text-amber-700 border-amber-200': p.approval_status === 'PENDING',
                    'bg-rose-50 text-danger border-rose-200': p.approval_status === 'REJECTED',
                  }"
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase"
                >
                  {{ p.approval_status }}
                </span>
              </td>
              <td class="py-4 px-6 text-text-muted">
                {{ p.created_at }}
              </td>
              <td class="py-4 px-6 text-right">
                <Link
                  :href="route('admin.products.show', p.id)"
                  class="px-3.5 py-1.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-dark transition shadow-xs"
                >
                  Tinjau Produk →
                </Link>
              </td>
            </tr>

            <tr v-if="products.data.length === 0">
              <td colspan="6" class="text-center py-12 text-text-muted">
                Tidak ada produk dalam status ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
