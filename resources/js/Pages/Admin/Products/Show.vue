<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  product: Object,
});

const isRejectModalOpen = ref(false);

const rejectForm = useForm({
  reason: '',
});

const submitApprove = () => {
  if (confirm(`Setujui produk "${props.product.name}" untuk tayang di katalog marketplace?`)) {
    useForm({}).post(route('admin.products.approve', props.product.id));
  }
};

const submitReject = () => {
  rejectForm.post(route('admin.products.reject', props.product.id), {
    onSuccess: () => {
      isRejectModalOpen.value = false;
    },
  });
};

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
    <Head :title="`Moderasi: ${product.name} — Admin Console`" />

    <!-- Top Navigation -->
    <div class="mb-6 flex items-center justify-between">
      <Link
        :href="route('admin.products.index')"
        class="text-xs font-bold text-text-muted hover:text-primary flex items-center gap-1.5"
      >
        ← Kembali ke Daftar Produk
      </Link>

      <div class="flex items-center gap-2">
        <span
          :class="{
            'bg-emerald-50 text-emerald-700 border-emerald-200': product.approval_status === 'APPROVED',
            'bg-amber-50 text-amber-700 border-amber-200': product.approval_status === 'PENDING',
            'bg-rose-50 text-danger border-rose-200': product.approval_status === 'REJECTED',
          }"
          class="px-3 py-1 rounded-full text-xs font-bold border uppercase"
        >
          Status: {{ product.approval_status }}
        </span>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Product Details (2 Cols) -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Header Info Card -->
        <div class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 bg-primary/10 text-primary text-xs font-bold rounded-lg uppercase">
              {{ product.product_type }}
            </span>
            <span v-if="product.tour && product.tour.includes_ferry" class="px-2.5 py-1 bg-secondary/10 text-secondary text-xs font-bold rounded-lg">
              🚢 Tiket Ferry Danau Toba Termasuk
            </span>
          </div>

          <h1 class="text-2xl font-black text-text-main">{{ product.name }}</h1>
          <p class="text-xs text-text-muted leading-relaxed">{{ product.short_description }}</p>

          <div class="pt-4 border-t border-border grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Tarif Dasar</span>
              <strong class="text-base font-black text-primary">{{ formatRupiah(product.base_price) }}</strong>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Skema Wajib DP</span>
              <strong class="text-sm font-bold text-emerald-700">{{ product.dp_percentage }}%</strong>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Lead Time Booking</span>
              <strong class="text-sm font-bold text-text-main">H-{{ product.min_lead_time_hours }} Jam</strong>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Status Publikasi</span>
              <strong class="text-sm font-bold text-text-main">{{ product.status }}</strong>
            </div>
          </div>
        </div>

        <!-- Tour Itinerary (If Tour) -->
        <div v-if="product.tour" class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <h2 class="text-base font-bold text-text-main">Rencana Perjalanan ({{ product.tour.duration_days }}H{{ product.tour.duration_nights }}M)</h2>

          <div class="space-y-4 text-xs">
            <div v-for="day in product.itinerary" :key="day.id" class="p-4 bg-surface-muted rounded-xl border border-border space-y-2">
              <span class="font-bold text-primary block text-sm">Hari ke-{{ day.day_number }}: {{ day.title }}</span>
              <p v-if="day.description" class="text-text-muted text-xs">{{ day.description }}</p>

              <div v-if="day.stops && day.stops.length > 0" class="pt-2 space-y-2">
                <div v-for="stop in day.stops" :key="stop.id" class="p-2.5 bg-white rounded-lg border border-border flex justify-between items-center">
                  <div>
                    <strong class="text-text-main">📍 {{ stop.title }}</strong>
                    <span v-if="stop.destination_name" class="text-text-muted text-[11px] block">Objek: {{ stop.destination_name }}</span>
                  </div>
                  <span class="text-[11px] text-text-muted font-medium">~{{ stop.estimated_minutes }} mnt</span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-border text-xs">
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
              <strong class="text-emerald-800 block mb-1">Inclusions:</strong>
              <p class="text-text-muted whitespace-pre-line">{{ product.tour.inclusions }}</p>
            </div>
            <div class="p-3 bg-rose-50 rounded-xl border border-rose-200">
              <strong class="text-danger block mb-1">Exclusions:</strong>
              <p class="text-text-muted whitespace-pre-line">{{ product.tour.exclusions }}</p>
            </div>
          </div>
        </div>

        <!-- Vehicle Specs (If Vehicle) -->
        <div v-if="product.vehicle" class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <h2 class="text-base font-bold text-text-main">Spesifikasi Armada Kendaraan</h2>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 bg-surface-muted rounded-xl border border-border">
              <span class="text-text-muted block font-semibold mb-0.5">Tipe Armada</span>
              <strong class="text-text-main">{{ product.vehicle.vehicle_type }}</strong>
            </div>
            <div class="p-3 bg-surface-muted rounded-xl border border-border">
              <span class="text-text-muted block font-semibold mb-0.5">Plat Nomor</span>
              <strong class="text-text-main font-mono">{{ product.vehicle.license_plate }}</strong>
            </div>
            <div class="p-3 bg-surface-muted rounded-xl border border-border">
              <span class="text-text-muted block font-semibold mb-0.5">Kapasitas</span>
              <strong class="text-text-main">{{ product.vehicle.passenger_capacity }} Orang / {{ product.vehicle.luggage_capacity }} Koper</strong>
            </div>
            <div class="p-3 bg-surface-muted rounded-xl border border-border">
              <span class="text-text-muted block font-semibold mb-0.5">Pengemudi</span>
              <strong class="text-text-main">{{ product.vehicle.with_driver ? 'Termasuk Driver' : 'Lepas Kunci' }}</strong>
            </div>
          </div>

          <div class="text-xs text-text-muted pt-2 leading-relaxed">
            <p>{{ product.full_description }}</p>
          </div>
        </div>

        <!-- Photos Gallery -->
        <div v-if="product.media && product.media.length > 0" class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <h2 class="text-base font-bold text-text-main">Foto Produk Terunggah</h2>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <img
              v-for="m in product.media"
              :key="m.id"
              :src="m.url"
              class="w-full h-32 object-cover rounded-xl border border-border shadow-xs"
            />
          </div>
        </div>
      </div>

      <!-- Right Column: Partner & Moderation Actions (1 Col) -->
      <div class="space-y-6">
        <!-- Moderation Action Panel -->
        <div class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <h3 class="text-base font-bold text-text-main border-b border-border pb-3">Aksi Moderasi Admin</h3>

          <div class="space-y-3">
            <button
              v-if="product.approval_status !== 'APPROVED'"
              @click="submitApprove"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2"
            >
              <span>✅</span>
              <span>Setujui & Publikasikan Produk</span>
            </button>

            <button
              v-if="product.approval_status !== 'REJECTED'"
              @click="isRejectModalOpen = true"
              class="w-full bg-danger hover:bg-rose-700 text-white font-bold py-3 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2"
            >
              <span>❌</span>
              <span>Tolak Produk (Berikan Alasan)</span>
            </button>

            <div v-if="product.approval_status === 'APPROVED'" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 font-semibold text-center">
              ✓ Produk telah aktif dan dapat dipesan oleh wisatawan di katalog publik.
            </div>
          </div>
        </div>

        <!-- Partner Profile Card -->
        <div class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-4">
          <h3 class="text-sm font-bold text-text-main border-b border-border pb-2">Informasi Mitra Penyedia</h3>

          <div class="space-y-3 text-xs">
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Nama Usaha / Brand</span>
              <strong class="text-sm text-text-main">{{ product.partner.business_name }}</strong>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Penanggung Jawab (PIC)</span>
              <span class="text-text-main">{{ product.partner.pic_name }} ({{ product.partner.pic_phone }})</span>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Kota Operasional</span>
              <span class="text-text-main">Kabupaten {{ product.partner.city }}</span>
            </div>
            <div>
              <span class="text-text-muted block font-semibold mb-0.5">Status Legalitas Mitra</span>
              <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                {{ product.partner.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Approval Logs -->
        <div v-if="product.logs && product.logs.length > 0" class="bg-surface rounded-2xl border border-border p-6 shadow-xs space-y-3">
          <h3 class="text-sm font-bold text-text-main border-b border-border pb-2">Riwayat Evaluasi</h3>

          <div class="space-y-3 text-xs">
            <div v-for="log in product.logs" :key="log.id" class="p-3 bg-surface-muted rounded-xl border border-border space-y-1">
              <div class="flex justify-between items-center">
                <strong class="text-text-main">{{ log.admin_name }}</strong>
                <span class="text-[10px] text-text-muted">{{ log.created_at }}</span>
              </div>
              <span class="text-[11px] font-bold text-primary block">{{ log.previous_status }} → {{ log.new_status }}</span>
              <p class="text-text-muted text-[11px]">{{ log.reason }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div
      v-if="isRejectModalOpen"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-lg w-full p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-text-main">Tolak Produk Katalog</h3>
        <p class="text-xs text-text-muted leading-relaxed">
          Tuliskan catatan perbaikan atau alasan penolakan secara jelas agar mitra dapat memperbaiki deskripsi atau informasi harga.
        </p>

        <form @submit.prevent="submitReject" class="space-y-4">
          <div>
            <textarea
              v-model="rejectForm.reason"
              rows="4"
              placeholder="Contoh: Deskripsi rute itinerary belum mencantumkan titik temu penyeberangan ferry Ajibata..."
              class="w-full text-xs font-medium rounded-xl border-border focus:border-danger focus:ring-danger"
              required
            ></textarea>
            <span v-if="rejectForm.errors.reason" class="text-danger text-xs mt-1 block">
              {{ rejectForm.errors.reason }}
            </span>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="isRejectModalOpen = false"
              class="px-4 py-2 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="rejectForm.processing"
              class="px-5 py-2 rounded-xl bg-danger text-white text-xs font-bold hover:bg-rose-700 disabled:opacity-50"
            >
              {{ rejectForm.processing ? 'Menyimpan...' : 'Kirim Penolakan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

