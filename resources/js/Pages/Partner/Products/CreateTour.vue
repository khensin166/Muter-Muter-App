<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  destinations: Array,
  tourTypes: Array,
});

const form = useForm({
  name: '',
  short_description: '',
  full_description: '',
  tour_type: 'PRIVATE_TRIP',
  base_price: '',
  pricing_type: 'PER_PERSON',
  duration_days: 1,
  duration_nights: 0,
  min_participants: 2,
  max_participants: 10,
  includes_ferry: true,
  inclusions: "- Tiket Ferry Penyeberangan Danau Toba PP\n- Transportasi Privat Mobil/Minibus\n- Driver merangkap Local Guide\n- Air mineral harian\n- Tiket objek wisata",
  exclusions: "- Tiket penerbangan ke Silangit/Kualanamu\n- Pengeluaran pribadi & tips driver\n- Makan di luar paket",
  terms_conditions: "Pemesanan minimal H-1 sebelum jadwal keberangkatan. DP 30% wajib dilunasi dalam waktu 24 jam setelah ketersediaan disetujui.",
  photos: [],
  itinerary: [
    {
      day_number: 1,
      title: 'Penjemputan & Eksplorasi Danau Toba',
      description: 'Penjemputan rombongan di titik temu dan perjalanan menuju destinasi utama.',
      stops: [
        {
          destination_id: props.destinations[0]?.id || '',
          title: 'Kunjungan Destinasi Ikonik',
          activity_description: 'Eksplorasi spot wisata dan foto pemandangan.',
          estimated_minutes: 90,
        },
      ],
    },
  ],
});

const addDay = () => {
  form.itinerary.push({
    day_number: form.itinerary.length + 1,
    title: `Hari ke-${form.itinerary.length + 1}`,
    description: '',
    stops: [],
  });
};

const removeDay = (index) => {
  form.itinerary.splice(index, 1);
  form.itinerary.forEach((d, i) => d.day_number = i + 1);
};

const addStop = (dayIndex) => {
  form.itinerary[dayIndex].stops.push({
    destination_id: props.destinations[0]?.id || '',
    title: 'Spot Kunjungan Baru',
    activity_description: '',
    estimated_minutes: 60,
  });
};

const removeStop = (dayIndex, stopIndex) => {
  form.itinerary[dayIndex].stops.splice(stopIndex, 1);
};

const handlePhotoUpload = (e) => {
  form.photos = Array.from(e.target.files);
};

const submit = () => {
  form.post(route('partner.tours.store'));
};
</script>

<template>
  <PartnerLayout>
    <Head title="Buat Paket Tour Baru — Mitra Vendor" />

    <div class="p-6 max-w-4xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">Katalog Produk</span>
          <h1 class="text-2xl font-black text-text-main">Tambah Paket Tour Wisata</h1>
          <p class="text-xs text-text-muted mt-0.5">Lengkapi informasi paket, jadwal perjalanan harian, dan kebijakan tur.</p>
        </div>

        <Link
          :href="route('partner.products.index')"
          class="px-3.5 py-2 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted transition"
        >
          ← Kembali ke Produk
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Section 1: Informasi Dasar -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <h2 class="text-sm font-bold text-text-main uppercase tracking-wider border-b border-border pb-2">
            1. Informasi Dasar Paket
          </h2>

          <div class="space-y-4 text-xs">
            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Nama Paket Tour *</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: 3D2N Eksplor Eksotik Danau Toba & Budaya Samosir"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
              <span v-if="form.errors.name" class="text-danger mt-1 block">{{ form.errors.name }}</span>
            </div>

            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Ringkasan Singkat (Short Description) *</label>
              <input
                v-model="form.short_description"
                type="text"
                placeholder="Ringkasan 1-2 kalimat untuk kartu pencarian marketplace"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
              <span v-if="form.errors.short_description" class="text-danger mt-1 block">{{ form.errors.short_description }}</span>
            </div>

            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Deskripsi Lengkap *</label>
              <textarea
                v-model="form.full_description"
                rows="4"
                placeholder="Jelajahi keunikan tur, rute perjalanan, pengalaman lokal, dan kenyamanan akomodasi..."
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              ></textarea>
              <span v-if="form.errors.full_description" class="text-danger mt-1 block">{{ form.errors.full_description }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Tipe Tour *</label>
                <select
                  v-model="form.tour_type"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                >
                  <option v-for="t in tourTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Durasi Hari *</label>
                <input
                  v-model.number="form.duration_days"
                  type="number"
                  min="1"
                  max="30"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Durasi Malam *</label>
                <input
                  v-model.number="form.duration_nights"
                  type="number"
                  min="0"
                  max="30"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Tarif Dasar (IDR) *</label>
                <input
                  v-model.number="form.base_price"
                  type="number"
                  step="1000"
                  min="10000"
                  placeholder="1450000"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Min Peserta *</label>
                <input
                  v-model.number="form.min_participants"
                  type="number"
                  min="1"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Maks Peserta *</label>
                <input
                  v-model.number="form.max_participants"
                  type="number"
                  min="1"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>
            </div>

            <!-- Ferry Integration Checkbox -->
            <div class="p-3 bg-secondary/10 rounded-xl border border-secondary/20 flex items-center gap-3">
              <input
                v-model="form.includes_ferry"
                id="ferry_toggle"
                type="checkbox"
                class="rounded text-secondary focus:ring-secondary w-4 h-4"
              />
              <label for="ferry_toggle" class="cursor-pointer">
                <span class="font-bold text-text-main block">🚢 Termasuk Tiket Ferry Penyeberangan Danau Toba</span>
                <span class="text-[11px] text-text-muted">Centang jika paket sudah mencakup penyeberangan kapal ferry (Ajibata - Tomok / Simanindo).</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Section 2: Itinerary Harian -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <div class="flex items-center justify-between border-b border-border pb-2">
            <h2 class="text-sm font-bold text-text-main uppercase tracking-wider">
              2. Jadwal Perjalanan (Itinerary Harian)
            </h2>
            <button
              type="button"
              @click="addDay"
              class="px-3 py-1 bg-primary text-white rounded-lg text-xs font-bold hover:bg-primary-dark transition"
            >
              + Tambah Hari
            </button>
          </div>

          <div class="space-y-6">
            <div
              v-for="(day, dIndex) in form.itinerary"
              :key="dIndex"
              class="p-4 bg-surface-muted rounded-xl border border-border space-y-3"
            >
              <div class="flex items-center justify-between">
                <span class="font-bold text-primary text-xs uppercase">Hari ke-{{ day.day_number }}</span>
                <button
                  type="button"
                  @click="removeDay(dIndex)"
                  v-if="form.itinerary.length > 1"
                  class="text-danger text-xs font-bold hover:underline"
                >
                  Hapus Hari
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Judul Hari</label>
                  <input
                    v-model="day.title"
                    type="text"
                    placeholder="Contoh: Eksplor Tomok & Samosir Timur"
                    class="w-full text-xs font-medium rounded-xl border-border bg-white"
                    required
                  />
                </div>
                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Ringkasan Aktivitas</label>
                  <input
                    v-model="day.description"
                    type="text"
                    placeholder="Contoh: Menyeberang dengan ferry dan menikmati budaya Batak."
                    class="w-full text-xs font-medium rounded-xl border-border bg-white"
                  />
                </div>
              </div>

              <!-- Stops in this day -->
              <div class="pt-2">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-[11px] font-bold text-text-muted uppercase">Spot Kunjungan / Stop</span>
                  <button
                    type="button"
                    @click="addStop(dIndex)"
                    class="text-xs font-bold text-secondary hover:underline"
                  >
                    + Tambah Stop
                  </button>
                </div>

                <div class="space-y-2">
                  <div
                    v-for="(stop, sIndex) in day.stops"
                    :key="sIndex"
                    class="p-3 bg-white rounded-lg border border-border flex flex-col sm:flex-row gap-2 items-center text-xs"
                  >
                    <input
                      v-model="stop.title"
                      type="text"
                      placeholder="Nama Kegiatan/Spot"
                      class="flex-1 text-xs font-medium rounded-lg border-border"
                      required
                    />
                    <select
                      v-model="stop.destination_id"
                      class="text-xs font-medium rounded-lg border-border sm:w-48"
                    >
                      <option value="">Spot Bebas (Non-Katalog)</option>
                      <option v-for="d in destinations" :key="d.id" :value="d.id">
                        {{ d.name }}
                      </option>
                    </select>
                    <input
                      v-model.number="stop.estimated_minutes"
                      type="number"
                      placeholder="Menit"
                      class="w-20 text-xs font-medium rounded-lg border-border"
                    />
                    <button
                      type="button"
                      @click="removeStop(dIndex, sIndex)"
                      class="text-danger text-xs font-bold px-2"
                    >
                      ✕
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 3: Fasilitas & Foto -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <h2 class="text-sm font-bold text-text-main uppercase tracking-wider border-b border-border pb-2">
            3. Fasilitas & Foto
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <label class="block font-bold text-emerald-700 uppercase mb-1">Fasilitas Termasuk (Inclusions)</label>
              <textarea
                v-model="form.inclusions"
                rows="4"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary"
              ></textarea>
            </div>
            <div>
              <label class="block font-bold text-rose-700 uppercase mb-1">Tidak Termasuk (Exclusions)</label>
              <textarea
                v-model="form.exclusions"
                rows="4"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary"
              ></textarea>
            </div>
          </div>

          <div class="text-xs">
            <label class="block font-bold text-text-muted uppercase mb-1">Upload Foto Tour (JPG/PNG, Max 5MB)</label>
            <input
              type="file"
              @change="handlePhotoUpload"
              multiple
              accept="image/*"
              class="w-full text-xs text-text-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer"
            />
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('partner.products.index')"
            class="px-6 py-3 rounded-xl border border-border text-xs font-bold hover:bg-surface-muted transition"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-8 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold transition shadow-lg disabled:opacity-50"
          >
            {{ form.processing ? 'Menyimpan...' : 'Simpan & Ajukan Verifikasi Admin' }}
          </button>
        </div>
      </form>
    </div>
  </PartnerLayout>
</template>

