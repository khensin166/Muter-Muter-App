<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import FlagIcon from '@/Components/FlagIcon.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  destinations: Array,
  tourTypes: Array,
});

const contentLang = ref('id');
const isTranslating = ref(false);

const form = useForm({
  name: '',
  name_en: '',
  short_description: '',
  short_description_en: '',
  full_description: '',
  full_description_en: '',
  tour_type: 'PRIVATE_TRIP',
  base_price: '',
  pricing_type: 'PER_PERSON',
  duration_days: 1,
  duration_nights: 0,
  min_participants: 2,
  max_participants: 10,
  includes_ferry: true,
  inclusions: "- Tiket Ferry Penyeberangan Danau Toba PP\n- Transportasi Privat Mobil/Minibus\n- Driver merangkap Local Guide\n- Air mineral harian\n- Tiket objek wisata",
  inclusions_en: '',
  exclusions: "- Tiket penerbangan ke Silangit/Kualanamu\n- Pengeluaran pribadi & tips driver\n- Makan di luar paket",
  exclusions_en: '',
  terms_conditions: "Pemesanan minimal H-1 sebelum jadwal keberangkatan. DP 30% wajib dilunasi dalam waktu 24 jam setelah ketersediaan disetujui.",
  terms_conditions_en: '',
  photos: [],
  itinerary: [
    {
      day_number: 1,
      title: 'Penjemputan & Eksplorasi Danau Toba',
      title_en: '',
      description: 'Penjemputan rombongan di titik temu dan perjalanan menuju destinasi utama.',
      description_en: '',
      stops: [
        {
          destination_id: props.destinations[0]?.id || '',
          title: 'Kunjungan Destinasi Ikonik',
          title_en: '',
          activity_description: 'Eksplorasi spot wisata dan foto pemandangan.',
          activity_description_en: '',
          estimated_minutes: 90,
        },
      ],
    },
  ],
});

const autoTranslateAll = async () => {
  if (!form.name && !form.short_description) {
    alert('Harap isi Nama Paket Tour atau Ringkasan Singkat (Bahasa Indonesia) terlebih dahulu.');
    return;
  }

  isTranslating.value = true;
  try {
    const fieldsToTranslate = {
      name: form.name,
      short_description: form.short_description,
      full_description: form.full_description,
      inclusions: form.inclusions,
      exclusions: form.exclusions,
      terms_conditions: form.terms_conditions,
    };

    form.itinerary.forEach((day, dIdx) => {
      fieldsToTranslate[`itinerary_day_${dIdx}_title`] = day.title;
      fieldsToTranslate[`itinerary_day_${dIdx}_desc`] = day.description;
      day.stops.forEach((stop, sIdx) => {
        fieldsToTranslate[`itinerary_stop_${dIdx}_${sIdx}_title`] = stop.title;
        fieldsToTranslate[`itinerary_stop_${dIdx}_${sIdx}_desc`] = stop.activity_description;
      });
    });

    const res = await axios.post('/api/translate-batch', {
      fields: fieldsToTranslate,
      source: 'id',
      target: 'en',
    });

    if (res.data && res.data.translations) {
      const t = res.data.translations;
      form.name_en = t.name || '';
      form.short_description_en = t.short_description || '';
      form.full_description_en = t.full_description || '';
      form.inclusions_en = t.inclusions || '';
      form.exclusions_en = t.exclusions || '';
      form.terms_conditions_en = t.terms_conditions || '';

      form.itinerary.forEach((day, dIdx) => {
        day.title_en = t[`itinerary_day_${dIdx}_title`] || '';
        day.description_en = t[`itinerary_day_${dIdx}_desc`] || '';
        day.stops.forEach((stop, sIdx) => {
          stop.title_en = t[`itinerary_stop_${dIdx}_${sIdx}_title`] || '';
          stop.activity_description_en = t[`itinerary_stop_${dIdx}_${sIdx}_desc`] || '';
        });
      });

      // Switch to English tab to review translated content
      contentLang.value = 'en';
    }
  } catch (err) {
    console.error('Translation error:', err);
  } finally {
    isTranslating.value = false;
  }
};

const addDay = () => {
  form.itinerary.push({
    day_number: form.itinerary.length + 1,
    title: `Hari ke-${form.itinerary.length + 1}`,
    title_en: `Day ${form.itinerary.length + 1}`,
    description: '',
    description_en: '',
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
    title_en: '',
    activity_description: '',
    activity_description_en: '',
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

      <!-- Bilingual Switcher & Auto-Translate Action Bar -->
      <div class="bg-surface p-4 rounded-2xl border border-border shadow-card flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <span class="text-xs font-bold text-text-muted uppercase tracking-wider">Bahasa Formulir:</span>
          <div class="inline-flex rounded-xl p-1 bg-surface-muted border border-border">
            <button
              type="button"
              @click="contentLang = 'id'"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5',
                contentLang === 'id' ? 'bg-white shadow text-primary font-black' : 'text-text-muted hover:text-text-main'
              ]"
            >
              <FlagIcon country="id" className="w-4 h-2.5" />
              <span>Indonesia</span> <span class="text-[10px] bg-primary/10 text-primary px-1.5 py-0.2 rounded font-semibold">Default</span>
            </button>
            <button
              type="button"
              @click="contentLang = 'en'"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5',
                contentLang === 'en' ? 'bg-white shadow text-secondary font-black' : 'text-text-muted hover:text-text-main'
              ]"
            >
              <FlagIcon country="en" className="w-4 h-2.5" />
              <span>English</span> <span v-if="form.name_en" class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.2 rounded font-semibold">Ready</span>
            </button>
          </div>
        </div>

        <button
          type="button"
          @click="autoTranslateAll"
          :disabled="isTranslating"
          class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-teal-600 to-primary hover:from-teal-700 hover:to-primary-dark text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition disabled:opacity-50"
        >
          <span v-if="isTranslating" class="animate-spin text-sm">⏳</span>
          <span v-else>✨</span>
          <span>{{ isTranslating ? 'Menerjemahkan via AI...' : 'Auto-Translate ID ➔ EN' }}</span>
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Section 1: Informasi Dasar -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <div class="flex items-center justify-between border-b border-border pb-2">
            <h2 class="text-sm font-bold text-text-main uppercase tracking-wider">
              1. Informasi Dasar Paket
            </h2>
            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5" :class="contentLang === 'id' ? 'bg-primary/10 text-primary' : 'bg-secondary/10 text-secondary'">
              <FlagIcon :country="contentLang" className="w-3.5 h-2.5" />
              <span>{{ contentLang === 'id' ? 'Bahasa Indonesia' : 'English Version' }}</span>
            </span>
          </div>

          <!-- Bilingual inputs for Basic Info -->
          <div class="space-y-4 text-xs">
            <!-- Name -->
            <div v-show="contentLang === 'id'">
              <label class="block font-bold text-text-muted uppercase mb-1">Nama Paket Tour (Bahasa Indonesia) *</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: 3D2N Eksplor Eksotik Danau Toba & Budaya Samosir"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
              <span v-if="form.errors.name" class="text-danger mt-1 block">{{ form.errors.name }}</span>
            </div>
            <div v-show="contentLang === 'en'">
              <label class="block font-bold text-secondary uppercase mb-1">Tour Package Name (English)</label>
              <input
                v-model="form.name_en"
                type="text"
                placeholder="e.g. 3D2N Exotic Lake Toba & Samosir Cultural Tour"
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary focus:ring-secondary bg-secondary/5"
              />
              <p class="text-[11px] text-text-muted mt-1">Jika dikosongkan, sistem akan otomatis menerjemahkan dari teks Indonesia.</p>
            </div>

            <!-- Short Description -->
            <div v-show="contentLang === 'id'">
              <label class="block font-bold text-text-muted uppercase mb-1">Ringkasan Singkat (Short Description - ID) *</label>
              <input
                v-model="form.short_description"
                type="text"
                placeholder="Ringkasan 1-2 kalimat untuk kartu pencarian marketplace"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
              <span v-if="form.errors.short_description" class="text-danger mt-1 block">{{ form.errors.short_description }}</span>
            </div>
            <div v-show="contentLang === 'en'">
              <label class="block font-bold text-secondary uppercase mb-1">Short Description (English)</label>
              <input
                v-model="form.short_description_en"
                type="text"
                placeholder="1-2 sentences overview for marketplace search cards"
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary focus:ring-secondary bg-secondary/5"
              />
            </div>

            <!-- Full Description -->
            <div v-show="contentLang === 'id'">
              <label class="block font-bold text-text-muted uppercase mb-1">Deskripsi Lengkap (Bahasa Indonesia) *</label>
              <textarea
                v-model="form.full_description"
                rows="4"
                placeholder="Jelajahi keunikan tur, rute perjalanan, pengalaman lokal, dan kenyamanan akomodasi..."
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              ></textarea>
              <span v-if="form.errors.full_description" class="text-danger mt-1 block">{{ form.errors.full_description }}</span>
            </div>
            <div v-show="contentLang === 'en'">
              <label class="block font-bold text-secondary uppercase mb-1">Full Description (English)</label>
              <textarea
                v-model="form.full_description_en"
                rows="4"
                placeholder="Explore the wonders of the tour, travel routes, authentic local experiences, and accommodations..."
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary focus:ring-secondary bg-secondary/5"
              ></textarea>
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
            <div>
              <h2 class="text-sm font-bold text-text-main uppercase tracking-wider">
                2. Jadwal Perjalanan (Itinerary Harian)
              </h2>
              <span class="text-[11px] text-text-muted inline-flex items-center gap-1.5 mt-0.5">
                Menampilkan formulir dalam:
                <FlagIcon :country="contentLang" className="w-3.5 h-2.5" />
                <strong class="text-text-main">{{ contentLang === 'id' ? 'Bahasa Indonesia' : 'English' }}</strong>
              </span>
            </div>
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

              <!-- Day inputs based on contentLang -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs" v-if="contentLang === 'id'">
                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Judul Hari (ID)</label>
                  <input
                    v-model="day.title"
                    type="text"
                    placeholder="Contoh: Eksplor Tomok & Samosir Timur"
                    class="w-full text-xs font-medium rounded-xl border-border bg-white"
                    required
                  />
                </div>
                <div>
                  <label class="block font-bold text-text-muted uppercase mb-1">Ringkasan Aktivitas (ID)</label>
                  <input
                    v-model="day.description"
                    type="text"
                    placeholder="Contoh: Menyeberang dengan ferry dan menikmati budaya Batak."
                    class="w-full text-xs font-medium rounded-xl border-border bg-white"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs" v-else>
                <div>
                  <label class="block font-bold text-secondary uppercase mb-1">Day Title (EN)</label>
                  <input
                    v-model="day.title_en"
                    type="text"
                    placeholder="e.g. Exploring Tomok & East Samosir"
                    class="w-full text-xs font-medium rounded-xl border-secondary/30 bg-white"
                  />
                </div>
                <div>
                  <label class="block font-bold text-secondary uppercase mb-1">Day Overview (EN)</label>
                  <input
                    v-model="day.description_en"
                    type="text"
                    placeholder="e.g. Crossing by ferry and immersing in Batak culture."
                    class="w-full text-xs font-medium rounded-xl border-secondary/30 bg-white"
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
                      v-if="contentLang === 'id'"
                      v-model="stop.title"
                      type="text"
                      placeholder="Nama Kegiatan/Spot (ID)"
                      class="flex-1 text-xs font-medium rounded-lg border-border"
                      required
                    />
                    <input
                      v-else
                      v-model="stop.title_en"
                      type="text"
                      placeholder="Activity/Spot Title (EN)"
                      class="flex-1 text-xs font-medium rounded-lg border-secondary/30 bg-secondary/5"
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

        <!-- Section 3: Fasilitas & Kebijakan -->
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <div class="flex items-center justify-between border-b border-border pb-2">
            <h2 class="text-sm font-bold text-text-main uppercase tracking-wider">
              3. Fasilitas & Kebijakan Tour
            </h2>
            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5" :class="contentLang === 'id' ? 'bg-primary/10 text-primary' : 'bg-secondary/10 text-secondary'">
              <FlagIcon :country="contentLang" className="w-3.5 h-2.5" />
              <span>{{ contentLang === 'id' ? 'Bahasa Indonesia' : 'English Version' }}</span>
            </span>
          </div>

          <!-- Inclusions & Exclusions -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs" v-if="contentLang === 'id'">
            <div>
              <label class="block font-bold text-emerald-700 uppercase mb-1">Fasilitas Termasuk (Inclusions - ID)</label>
              <textarea
                v-model="form.inclusions"
                rows="4"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary"
              ></textarea>
            </div>
            <div>
              <label class="block font-bold text-rose-700 uppercase mb-1">Tidak Termasuk (Exclusions - ID)</label>
              <textarea
                v-model="form.exclusions"
                rows="4"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary"
              ></textarea>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs" v-else>
            <div>
              <label class="block font-bold text-emerald-700 uppercase mb-1">Included Amenities (Inclusions - EN)</label>
              <textarea
                v-model="form.inclusions_en"
                rows="4"
                placeholder="Included tour amenities..."
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary bg-secondary/5"
              ></textarea>
            </div>
            <div>
              <label class="block font-bold text-rose-700 uppercase mb-1">Not Included (Exclusions - EN)</label>
              <textarea
                v-model="form.exclusions_en"
                rows="4"
                placeholder="Exclusions..."
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary bg-secondary/5"
              ></textarea>
            </div>
          </div>

          <!-- Terms & Conditions -->
          <div class="text-xs" v-if="contentLang === 'id'">
            <label class="block font-bold text-text-muted uppercase mb-1">Syarat & Ketentuan / Kebijakan Pembatalan (ID)</label>
            <textarea
              v-model="form.terms_conditions"
              rows="3"
              class="w-full text-xs font-medium rounded-xl border-border focus:border-primary"
            ></textarea>
          </div>
          <div class="text-xs" v-else>
            <label class="block font-bold text-secondary uppercase mb-1">Terms & Conditions / Cancellation Policy (EN)</label>
            <textarea
              v-model="form.terms_conditions_en"
              rows="3"
              placeholder="Booking conditions, DP rules, and cancellation terms..."
              class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary bg-secondary/5"
            ></textarea>
          </div>

          <div class="text-xs pt-2">
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

