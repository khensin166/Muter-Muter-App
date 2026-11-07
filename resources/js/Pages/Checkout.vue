<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
  product: Object,
  initialConfig: Object,
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const form = useForm({
  product_id: props.product.id,
  service_start_date: '',
  service_start_time: '08:00',
  participant_count: props.initialConfig.participants || 2,
  rental_days: props.initialConfig.rental_days || 1,
  pickup_location: 'Pelabuhan Ajibata / Parapat',
  dropoff_location: 'Pelabuhan Ajibata / Parapat',
  special_requests: '',
  driver_notes: '',
});

// Price recalculations
const calculatedQuantity = computed(() => {
  return props.product.product_type === 'TOUR'
    ? form.participant_count
    : form.rental_days;
});

const calculatedTotalPrice = computed(() => {
  return props.product.base_price * calculatedQuantity.value;
});

const calculatedDpAmount = computed(() => {
  return calculatedTotalPrice.value * (props.product.dp_percentage / 100);
});

const calculatedBalanceAmount = computed(() => {
  return calculatedTotalPrice.value - calculatedDpAmount.value;
});

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0);
};

// Calculate minimum allowed date for datepicker
const minDate = computed(() => {
  const date = new Date();
  if (props.product.product_type === 'TOUR') {
    date.setDate(date.getDate() + 1); // Minimum H-1
  } else {
    date.setHours(date.getHours() + 12); // Minimum H-12 hours
  }
  return date.toISOString().split('T')[0];
});

const submit = () => {
  form.post(route('checkout.store'));
};
</script>

<template>
  <AppLayout>
    <Head title="Pengajuan Pemesanan — BetaTudia" />

    <div class="bg-surface-muted border-b border-border py-4 px-4 sm:px-6 lg:px-8">
      <div class="max-w-6xl mx-auto flex items-center gap-2 text-xs text-text-muted">
        <Link href="/" class="hover:text-primary">Beranda</Link>
        <span>/</span>
        <Link :href="product.product_type === 'TOUR' ? '/tours' : '/vehicles'" class="hover:text-primary">
          {{ product.product_type === 'TOUR' ? 'Paket Wisata' : 'Rental Armada' }}
        </Link>
        <span>/</span>
        <span class="text-text-main font-semibold">Formulir Checkout</span>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Guest Notice Banner if not logged in -->
      <div v-if="!authUser" class="mb-6 p-4 bg-amber-50 rounded-2xl border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5 text-amber-900">
          <span class="text-xl">👤</span>
          <div>
            <strong>Anda belum masuk ke akun.</strong>
            <p class="text-amber-700 mt-0.5">Silakan login atau daftar akun wisatawan untuk menyimpan bukti booking dan verifikasi DP.</p>
          </div>
        </div>
        <div class="flex gap-2">
          <Link :href="route('login')" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition">
            Masuk / Login
          </Link>
          <Link :href="route('register')" class="px-3.5 py-1.5 rounded-xl bg-white border border-amber-300 text-amber-900 font-bold hover:bg-amber-100 transition">
            Daftar Akun Baru
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Column (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-5">
            <h1 class="text-xl font-black text-text-main border-b border-border pb-3">
              1. Detail Jadwal & Informasi Pelanggan
            </h1>

            <form @submit.prevent="submit" id="checkout-form" class="space-y-4 text-xs">
              <!-- Lead time requirement notice -->
              <div class="p-3 bg-secondary/10 rounded-xl border border-secondary/20 text-text-main flex items-center gap-2">
                <span>⏱️</span>
                <span>
                  Ketentuan lead-time: Minimal <strong>{{ product.min_lead_time_hours >= 24 ? (product.min_lead_time_hours / 24) + ' hari' : product.min_lead_time_hours + ' jam' }}</strong> sebelum jadwal keberangkatan.
                </span>
              </div>

              <!-- Date Selection -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Tanggal Keberangkatan / Mulai *</label>
                  <input
                    v-model="form.service_start_date"
                    :min="minDate"
                    type="date"
                    class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                    required
                  />
                  <span v-if="form.errors.service_start_date" class="text-danger mt-1 block font-semibold">
                    {{ form.errors.service_start_date }}
                  </span>
                </div>

                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Waktu Keberangkatan / Jam Jemput</label>
                  <input
                    v-model="form.service_start_time"
                    type="time"
                    class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  />
                </div>
              </div>

              <!-- Tour specific fields -->
              <div v-if="product.product_type === 'TOUR'" class="space-y-4 pt-2">
                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">
                    Jumlah Peserta (Min: {{ product.tour.min_participants }}, Maks: {{ product.tour.max_participants }} orang) *
                  </label>
                  <input
                    v-model.number="form.participant_count"
                    type="number"
                    :min="product.tour.min_participants"
                    :max="product.tour.max_participants"
                    class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                    required
                  />
                </div>

                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Catatan Khusus / Permintaan Penjemputan</label>
                  <textarea
                    v-model="form.special_requests"
                    rows="3"
                    placeholder="Contoh: Titik jemput di Bandara Silangit jam 09.00 pagi, membawa 2 anak balita."
                    class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  ></textarea>
                </div>
              </div>

              <!-- Vehicle specific fields -->
              <div v-else class="space-y-4 pt-2">
                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Durasi Sewa (Hari) *</label>
                  <input
                    v-model.number="form.rental_days"
                    type="number"
                    min="1"
                    max="30"
                    class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                    required
                  />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block font-bold text-text-muted uppercase mb-1">Lokasi Titik Jemput *</label>
                    <input
                      v-model="form.pickup_location"
                      type="text"
                      placeholder="Bandara Silangit / Medan / Hotel Parapat"
                      class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                      required
                    />
                  </div>

                  <div>
                    <label class="block font-bold text-text-muted uppercase mb-1">Lokasi Titik Antar / Pengembalian *</label>
                    <input
                      v-model="form.dropoff_location"
                      type="text"
                      placeholder="Bandara Silangit / Samosir"
                      class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Catatan Tambahan untuk Driver</label>
                  <textarea
                    v-model="form.driver_notes"
                    rows="3"
                    placeholder="Contoh: Rute penyeberangan Ajibata-Tomok, minta driver yang paham jalur bukit."
                    class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  ></textarea>
                </div>
              </div>
            </form>
          </div>

          <!-- Cancellation Policy Snapshot Info -->
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-card text-xs space-y-3">
            <h3 class="font-bold text-text-main uppercase tracking-wider">Ketentuan Pembatalan & Refund Resmi</h3>
            <p class="text-text-muted leading-relaxed">
              Seluruh refund dihitung secara transparan dari <strong>nominal DP yang sudah Anda bayarkan</strong>:
            </p>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                <strong class="text-emerald-800 block">> 7 Hari</strong>
                <span class="text-[11px] text-emerald-600">Refund 100% DP</span>
              </div>
              <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                <strong class="text-amber-800 block">3 - 7 Hari</strong>
                <span class="text-[11px] text-amber-600">Refund 50% DP</span>
              </div>
              <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200">
                <strong class="text-rose-800 block">&lt; 3 Hari</strong>
                <span class="text-[11px] text-rose-600">Refund 0%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary & Sticky Sidebar (1 Col) -->
        <div class="space-y-6">
          <div class="bg-surface p-6 rounded-2xl border border-border shadow-xl space-y-5">
            <h2 class="text-base font-bold text-text-main border-b border-border pb-3">Ringkasan Pesanan</h2>

            <!-- Product Card Preview -->
            <div class="flex gap-3 items-center">
              <img
                :src="product.media_url || 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=200&q=80'"
                :alt="product.name"
                class="w-16 h-16 rounded-xl object-cover shrink-0"
              />
              <div class="min-w-0">
                <h3 class="font-bold text-text-main text-xs line-clamp-2">{{ product.name }}</h3>
                <span class="text-[11px] text-text-muted block mt-0.5">Mitra: {{ product.partner_name }} ({{ product.partner_city }})</span>
                <span v-if="product.tour && product.tour.includes_ferry" class="text-[10px] text-emerald-700 font-bold block">
                  🚢 Ferry Termasuk
                </span>
              </div>
            </div>

            <!-- Price Breakdown Calculation -->
            <div class="p-4 bg-surface-muted rounded-xl border border-border space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-text-muted">Tarif Satuan:</span>
                <span class="font-medium text-text-main">{{ formatRupiah(product.base_price) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-text-muted">Jumlah:</span>
                <span class="font-medium text-text-main">
                  {{ calculatedQuantity }} {{ product.product_type === 'TOUR' ? 'Orang' : 'Hari' }}
                </span>
              </div>
              <div class="flex justify-between font-bold pt-2 border-t border-border">
                <span class="text-text-main">Total Harga Pesanan:</span>
                <span class="text-primary text-sm font-black">{{ formatRupiah(calculatedTotalPrice) }}</span>
              </div>
              <div class="flex justify-between text-emerald-700 font-bold pt-1">
                <span>Wajib DP Awal ({{ product.dp_percentage }}%):</span>
                <span>{{ formatRupiah(calculatedDpAmount) }}</span>
              </div>
              <div class="flex justify-between text-text-muted text-[11px]">
                <span>Pelunasan s/d Hari-H:</span>
                <span>{{ formatRupiah(calculatedBalanceAmount) }}</span>
              </div>
            </div>

            <!-- 24h SLA Notice -->
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1">
              <div class="flex items-center gap-1.5 font-bold">
                <span>⚡</span>
                <span>SLA Konfirmasi Mitra 24 Jam</span>
              </div>
              <p class="text-[11px] text-amber-700 leading-relaxed">
                Setelah formulir dikirim, mitra vendor memiliki waktu 24 jam untuk memeriksa ketersediaan. Setelah disetujui, Anda diberikan waktu 24 jam untuk transfer DP.
              </p>
            </div>

            <!-- Submit Button -->
            <button
              v-if="authUser"
              type="submit"
              form="checkout-form"
              :disabled="form.processing"
              class="w-full bg-accent hover:bg-accent-hover text-white font-bold py-3.5 px-4 rounded-xl text-center text-sm transition-colors shadow-lg disabled:opacity-50"
            >
              {{ form.processing ? 'Mengajukan...' : 'Kirim Pengajuan Booking' }}
            </button>
            <Link
              v-else
              :href="route('login')"
              class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3.5 px-4 rounded-xl text-center text-sm transition-colors shadow-lg block"
            >
              Masuk untuk Mengajukan Booking
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

