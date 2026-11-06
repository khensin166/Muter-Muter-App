<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  bookings: Object,
  counts: Object,
  filters: Object,
});

const isCancelModalOpen = ref(false);
const selectedBookingId = ref(null);

const cancelForm = useForm({
  reason: '',
});

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};

const forceApprove = (id, code) => {
  if (confirm(`Setujui pesanan ${code} secara manual atas nama mitra? Tagihan DP dan hold 24 jam akan langsung diterbitkan.`)) {
    useForm({}).post(route('admin.sla.approve', id));
  }
};

const openCancelModal = (id) => {
  selectedBookingId.value = id;
  isCancelModalOpen.value = true;
};

const submitCancel = () => {
  cancelForm.post(route('admin.sla.cancel', selectedBookingId.value), {
    onSuccess: () => {
      isCancelModalOpen.value = false;
      selectedBookingId.value = null;
      cancelForm.reset();
    },
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Intervensi SLA 24 Jam — Admin Console" />

    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-text-main">Pusat Intervensi SLA Timeout 24 Jam</h1>
        <p class="text-xs text-text-muted mt-0.5">
          Daftar pesanan yang melewati batas waktu konfirmasi vendor 24 jam. Admin melakukan follow up via telepon/WA atau intervensi manual.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-rose-50 text-danger border border-rose-200">
          🚨 {{ counts.manual_review }} Pesanan Melanggar SLA
        </span>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
      <Link
        :href="route('admin.sla.index', { status: 'MANUAL_REVIEW' })"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0',
          filters.status === 'MANUAL_REVIEW'
            ? 'bg-primary text-white shadow-xs'
            : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
        ]"
      >
        <span>Perlu Intervensi Admin</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-danger">
          {{ counts.manual_review }}
        </span>
      </Link>
      <Link
        :href="route('admin.sla.index', { status: 'PENDING_APPROVAL' })"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0',
          filters.status === 'PENDING_APPROVAL'
            ? 'bg-primary text-white shadow-xs'
            : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
        ]"
      >
        <span>Sedang Berjalan (SLA Mitra)</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
          {{ counts.pending_approval }}
        </span>
      </Link>
    </div>

    <!-- Table -->
    <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
      <div class="p-6 border-b border-border flex items-center justify-between">
        <h2 class="text-base font-bold text-text-main">
          Antrean Pesanan ({{ bookings.total }})
        </h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-border bg-surface-muted/50 text-[11px] font-bold text-text-muted uppercase tracking-wider">
              <th class="py-3 px-6">Kode & Pelanggan</th>
              <th class="py-3 px-6">Mitra Penanggung Jawab</th>
              <th class="py-3 px-6">Produk</th>
              <th class="py-3 px-6">Tgl Keberangkatan</th>
              <th class="py-3 px-6">Status SLA</th>
              <th class="py-3 px-6 text-right">Aksi Intervensi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="b in bookings.data" :key="b.id" class="hover:bg-surface-muted/30 transition">
              <td class="py-4 px-6">
                <span class="font-mono font-bold text-primary block">{{ b.booking_code }}</span>
                <span class="font-bold text-text-main">{{ b.customer_name }}</span>
                <span class="text-text-muted text-[11px] block">{{ b.customer_phone }}</span>
              </td>

              <td class="py-4 px-6">
                <strong class="text-text-main block">{{ b.partner_name }}</strong>
                <a :href="`https://wa.me/${b.partner_phone}`" target="_blank" class="text-emerald-700 font-bold text-[11px] hover:underline">
                  Hubungi PIC: {{ b.partner_phone }} 💬
                </a>
              </td>

              <td class="py-4 px-6">
                <span class="font-medium text-text-main block">{{ b.product_name }}</span>
                <span class="text-text-muted text-[11px]">{{ formatRupiah(b.total_price) }}</span>
              </td>

              <td class="py-4 px-6 text-text-main font-medium">
                {{ b.service_start_date }}
              </td>

              <td class="py-4 px-6">
                <span
                  :class="b.booking_status === 'MANUAL_REVIEW' ? 'bg-rose-100 text-danger border-rose-300' : 'bg-amber-100 text-amber-800 border-amber-300'"
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase"
                >
                  {{ b.booking_status_label }}
                </span>
              </td>

              <td class="py-4 px-6 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="forceApprove(b.id, b.booking_code)"
                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs"
                  >
                    ✓ Force Approve
                  </button>
                  <button
                    @click="openCancelModal(b.id)"
                    class="px-3 py-1.5 rounded-xl bg-danger hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs"
                  >
                    ✕ Batalkan
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="bookings.data.length === 0">
              <td colspan="6" class="text-center py-12 text-text-muted">
                Tidak ada pesanan yang melanggar SLA 24 jam. Semua mitra merespons tepat waktu! 🎉
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Cancel Modal -->
    <div
      v-if="isCancelModalOpen"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-lg w-full p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-text-main">Batalkan Pesanan (Intervensi Admin)</h3>
        <p class="text-xs text-text-muted leading-relaxed">
          Tuliskan alasan pembatalan resmi yang akan dikirimkan ke pelanggan dan mitra penyedia.
        </p>

        <form @submit.prevent="submitCancel" class="space-y-4">
          <div>
            <textarea
              v-model="cancelForm.reason"
              rows="4"
              placeholder="Contoh: Mitra tidak dapat dihubungi dan armada pada tanggal tersebut tidak tersedia..."
              class="w-full text-xs font-medium rounded-xl border-border focus:border-danger focus:ring-danger"
              required
            ></textarea>
            <span v-if="cancelForm.errors.reason" class="text-danger text-xs mt-1 block">
              {{ cancelForm.errors.reason }}
            </span>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="isCancelModalOpen = false"
              class="px-4 py-2 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="cancelForm.processing"
              class="px-5 py-2 rounded-xl bg-danger text-white text-xs font-bold hover:bg-rose-700 disabled:opacity-50"
            >
              {{ cancelForm.processing ? 'Menyimpan...' : 'Batalkan Pesanan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

