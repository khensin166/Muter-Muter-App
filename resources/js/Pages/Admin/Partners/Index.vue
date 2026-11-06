<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  partners: Array,
  currentStatus: String,
  counts: Object,
});

const statusTabs = [
  { key: 'PENDING', label: 'Menunggu Verifikasi', count: props.counts.pending, color: 'text-amber-700 bg-amber-50' },
  { key: 'APPROVED', label: 'Disetujui (Aktif)', count: props.counts.approved, color: 'text-emerald-700 bg-emerald-50' },
  { key: 'SUSPENDED', label: 'Disuspend', count: props.counts.suspended, color: 'text-slate-700 bg-slate-50' },
  { key: 'REJECTED', label: 'Ditolak', count: props.counts.rejected, color: 'text-danger bg-rose-50' },
  { key: 'ALL', label: 'Semua Mitra', count: null, color: '' },
];
</script>

<template>
  <AdminLayout>
    <Head title="Verifikasi Mitra & KYC — Admin Console" />

    <!-- Header Section -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-text-main">Verifikasi Mitra & Dokumen KYC</h1>
        <p class="text-xs text-text-muted mt-0.5">
          Tinjau pendaftaran operator tour dan penyedia kendaraan lokal sebelum mengizinkan publikasi paket wisata ke publik.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-purple-50 text-purple-800 border border-purple-200">
          KYC Compliance Engine
        </span>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
      <Link
        v-for="tab in statusTabs"
        :key="tab.key"
        :href="route('admin.partners.index', { status: tab.key })"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0',
          currentStatus === tab.key
            ? 'bg-primary text-white shadow-xs'
            : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
        ]"
      >
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.count !== null"
          :class="[
            'px-2 py-0.5 rounded-full text-[10px] font-black',
            currentStatus === tab.key ? 'bg-white/20 text-white' : tab.color
          ]"
        >
          {{ tab.count }}
        </span>
      </Link>
    </div>

    <!-- Partners Table -->
    <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
      <div class="p-6 border-b border-border flex items-center justify-between">
        <h2 class="text-base font-bold text-text-main">
          Daftar Mitra ({{ partners.length }})
        </h2>
      </div>

      <div v-if="partners.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-background text-text-muted uppercase tracking-wider text-[11px] border-b border-border">
            <tr>
              <th class="p-4 pl-6">Nama Badan Usaha / Brand</th>
              <th class="p-4">PIC & Kontak</th>
              <th class="p-4">Wilayah Operasional</th>
              <th class="p-4">Status Akun</th>
              <th class="p-4">Staf / Produk</th>
              <th class="p-4">Tanggal Daftar</th>
              <th class="p-4 pr-6 text-right">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="p in partners" :key="p.id" class="hover:bg-surface-muted/50 transition">
              <td class="p-4 pl-6">
                <span class="font-bold text-primary block text-sm">{{ p.business_name }}</span>
                <span v-if="p.brand_name" class="text-text-muted text-[11px]">Brand: {{ p.brand_name }}</span>
              </td>
              <td class="p-4">
                <div class="font-semibold text-text-main">{{ p.pic_name }}</div>
                <div class="text-[11px] text-text-muted">{{ p.pic_phone }}</div>
                <div class="text-[11px] text-text-muted">{{ p.email }}</div>
              </td>
              <td class="p-4 font-medium text-text-main">
                {{ p.city }}
              </td>
              <td class="p-4">
                <span
                  :class="[
                    'px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase',
                    p.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' :
                    p.status === 'PENDING' ? 'bg-amber-100 text-amber-800' :
                    p.status === 'REJECTED' ? 'bg-rose-100 text-danger' :
                    'bg-slate-100 text-slate-700'
                  ]"
                >
                  {{ p.status }}
                </span>
              </td>
              <td class="p-4 text-text-muted">
                <div>👥 {{ p.members_count }} Staf</div>
                <div>🎒 {{ p.products_count }} Produk</div>
              </td>
              <td class="p-4 text-text-muted text-[11px]">
                {{ p.registered_at }}
              </td>
              <td class="p-4 pr-6 text-right">
                <Link
                  :href="route('admin.partners.show', p.id)"
                  class="inline-block bg-primary hover:bg-primary-dark text-white font-bold py-1.5 px-3 rounded-lg text-xs transition"
                >
                  Periksa Dokumen →
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="p-10 text-center text-text-muted text-xs">
        <span>Tidak ada mitra pada status ini.</span>
      </div>
    </div>
  </AdminLayout>
</template>

