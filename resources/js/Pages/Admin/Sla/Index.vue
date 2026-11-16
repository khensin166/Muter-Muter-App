<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  bookings: Object,
  counts: Object,
  partners: Array,
  filters: Object,
});

const isCancelModalOpen = ref(false);
const isReassignModalOpen = ref(false);
const selectedBooking = ref(null);

const cancelForm = useForm({
  reason: '',
});

const reassignForm = useForm({
  partner_id: '',
  assigned_driver_name: '',
  assigned_driver_phone: '',
  assigned_vehicle_plate: '',
  reason: '',
});

const forceApprove = (id, code) => {
  if (confirm(`Setujui pesanan ${code} secara manual atas nama mitra? Tagihan DP dan hold 24 jam akan langsung diterbitkan.`)) {
    useForm({}).post(route('admin.sla.approve', id));
  }
};

const openCancelModal = (booking) => {
  selectedBooking.value = booking;
  isCancelModalOpen.value = true;
};

const submitCancel = () => {
  cancelForm.post(route('admin.sla.cancel', selectedBooking.value.id), {
    onSuccess: () => {
      isCancelModalOpen.value = false;
      selectedBooking.value = null;
      cancelForm.reset();
    },
  });
};

const openReassignModal = (booking) => {
  selectedBooking.value = booking;
  reassignForm.partner_id = booking.partner_id || '';
  reassignForm.assigned_driver_name = booking.assigned_driver_name || '';
  reassignForm.assigned_driver_phone = booking.assigned_driver_phone || '';
  reassignForm.assigned_vehicle_plate = booking.assigned_vehicle_plate || '';
  reassignForm.reason = '';
  isReassignModalOpen.value = true;
};

const submitReassign = () => {
  reassignForm.post(route('admin.sla.reassign', selectedBooking.value.id), {
    onSuccess: () => {
      isReassignModalOpen.value = false;
      selectedBooking.value = null;
      reassignForm.reset();
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
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 tactile-press',
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
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 tactile-press',
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
      <Link
        :href="route('admin.sla.index', { status: 'ASSIGNED_TO_PARTNER' })"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 tactile-press',
          filters.status === 'ASSIGNED_TO_PARTNER'
            ? 'bg-primary text-white shadow-xs'
            : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
        ]"
      >
        <span>Limpahan Penugasan Platform</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
          {{ counts.assigned_to_partner }}
        </span>
      </Link>
      <Link
        :href="route('admin.sla.index', { status: 'ALL' })"
        :class="[
          'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 tactile-press',
          filters.status === 'ALL'
            ? 'bg-primary text-white shadow-xs'
            : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
        ]"
      >
        <span>Semua Pesanan</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-200 text-slate-700">
          {{ counts.all }}
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
              <th class="py-3 px-6">Tgl Layanan</th>
              <th class="py-3 px-6">Status SLA & Riwayat</th>
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
                <strong class="text-text-main block">{{ b.partner_name || 'Belum Ditugaskan' }}</strong>
                <span class="text-text-muted text-[11px] block">{{ b.partner_city }}</span>
                <a v-if="b.partner_phone" :href="`https://wa.me/${b.partner_phone}`" target="_blank" class="text-emerald-700 font-bold text-[11px] hover:underline block mt-0.5">
                  Hubungi PIC: {{ b.partner_phone }} 💬
                </a>
                <span v-if="b.is_reassigned" class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                  🔄 Dialihkan dari: {{ b.original_partner_name }}
                </span>
              </td>

              <td class="py-4 px-6">
                <span class="font-medium text-text-main block">{{ b.product_name }}</span>
                <span class="text-text-muted text-[11px]">{{ formatRupiah(b.total_price) }}</span>
                <span class="inline-block text-[10px] uppercase font-bold text-primary px-1.5 py-0.5 rounded bg-primary/10 mt-1">
                  {{ b.product_type }}
                </span>
              </td>

              <td class="py-4 px-6 text-text-main font-medium">
                {{ b.service_start_date }}
              </td>

              <td class="py-4 px-6 space-y-1.5">
                <div>
                  <span
                    :class="[
                      b.booking_status === 'MANUAL_REVIEW' ? 'bg-rose-100 text-danger border-rose-300' :
                      b.booking_status === 'ASSIGNED_TO_PARTNER' ? 'bg-blue-100 text-blue-800 border-blue-300' :
                      'bg-amber-100 text-amber-800 border-amber-300'
                    ]"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase inline-block"
                  >
                    {{ b.booking_status_label }}
                  </span>
                </div>

                <!-- Rejection note alert if previously rejected by vendor -->
                <div v-if="b.rejection_reason" class="p-2 rounded-lg bg-rose-50 border border-rose-200 text-danger text-[11px]">
                  <strong>Ditolak mitra:</strong> {{ b.rejection_reason }}
                </div>
              </td>

              <td class="py-4 px-6 text-right">
                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                  <button
                    @click="openReassignModal(b)"
                    title="Alihkan pesanan ke mitra rekanan lain"
                    class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs tactile-press cursor-pointer flex items-center gap-1"
                  >
                    <span>🔄 Alihkan Mitra</span>
                  </button>

                  <button
                    @click="forceApprove(b.id, b.booking_code)"
                    title="Setujui manual langsung buka tagihan DP"
                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs tactile-press cursor-pointer flex items-center gap-1"
                  >
                    <span>✓ Force Approve</span>
                  </button>

                  <button
                    @click="openCancelModal(b)"
                    title="Batalkan pesanan pelanggan"
                    class="px-3 py-1.5 rounded-xl bg-danger hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs tactile-press cursor-pointer"
                  >
                    <span>✕ Batalkan</span>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="bookings.data.length === 0">
              <td colspan="6" class="text-center py-12 text-text-muted">
                Tidak ada pesanan pada filter ini. Operasional berjalan lancar! 🎉
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Reassign Modal (Hybrid Dispatching Console) -->
    <div
      v-if="isReassignModalOpen"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-3xl border border-border max-w-xl w-full p-6 shadow-2xl space-y-5 animate-in fade-in duration-200">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-black text-text-main flex items-center gap-2">
              <span>🔄 Alihkan / Tugaskan ke Mitra Baru</span>
            </h3>
            <p class="text-xs text-text-muted mt-1">
              Pindahkan pesanan <strong class="text-primary font-mono">{{ selectedBooking?.booking_code }}</strong> ({{ selectedBooking?.product_name }}) ke mitra terverifikasi lain agar pesanan terselamatkan.
            </p>
          </div>
          <button @click="isReassignModalOpen = false" class="text-text-muted hover:text-text-main font-bold p-1">✕</button>
        </div>

        <form @submit.prevent="submitReassign" class="space-y-4 text-xs">
          <!-- Pilih Mitra -->
          <div>
            <label class="block font-bold text-text-main mb-1.5">Pilih Mitra Pengganti / Penerima Tugas <span class="text-danger">*</span></label>
            <select
              v-model="reassignForm.partner_id"
              class="w-full text-xs font-medium rounded-xl border-border bg-surface-muted/30 focus:border-primary focus:ring-primary py-2.5 px-3"
              required
            >
              <option value="" disabled>-- Pilih Mitra Terverifikasi --</option>
              <option v-for="p in partners" :key="p.id" :value="p.id">
                {{ p.business_name }} ({{ p.city }}) — ★ {{ p.average_rating || '5.0' }} (PIC: {{ p.pic_phone }})
              </option>
            </select>
            <span v-if="reassignForm.errors.partner_id" class="text-danger text-xs mt-1 block">
              {{ reassignForm.errors.partner_id }}
            </span>
          </div>

          <!-- Driver Name & Phone & Plate (Optional) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-text-main mb-1">Nama Supir / Guide</label>
              <input
                v-model="reassignForm.assigned_driver_name"
                type="text"
                placeholder="Contoh: Ucok Silalahi"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary py-2 px-3"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">No. HP Supir</label>
              <input
                v-model="reassignForm.assigned_driver_phone"
                type="text"
                placeholder="0812xxxx"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary py-2 px-3"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">Plat Nomor</label>
              <input
                v-model="reassignForm.assigned_vehicle_plate"
                type="text"
                placeholder="BK 1234 ABC"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary py-2 px-3"
              />
            </div>
          </div>

          <!-- Alasan / Catatan Penugasan -->
          <div>
            <label class="block font-bold text-text-main mb-1.5">Alasan Pengalihan / Catatan Khusus untuk Mitra</label>
            <textarea
              v-model="reassignForm.reason"
              rows="3"
              placeholder="Contoh: Unit mitra sebelumnya sedang mengalami servis berkala; dialihkan agar jadwal penjemputan tamu aman."
              class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary p-3"
            ></textarea>
            <span v-if="reassignForm.errors.reason" class="text-danger text-xs mt-1 block">
              {{ reassignForm.errors.reason }}
            </span>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-border">
            <button
              type="button"
              @click="isReassignModalOpen = false"
              class="px-4 py-2.5 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted tactile-press cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="reassignForm.processing"
              class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md disabled:opacity-50 tactile-press cursor-pointer"
            >
              {{ reassignForm.processing ? 'Memproses Penugasan...' : '✓ Tugaskan Sekarang' }}
            </button>
          </div>
        </form>
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

