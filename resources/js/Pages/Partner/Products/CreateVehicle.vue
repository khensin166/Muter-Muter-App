<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  vehicleTypes: Array,
  transmissions: Array,
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
  vehicle_type: 'CAR',
  brand: 'Toyota',
  model: 'Innova Reborn 2.4 V Diesel',
  manufacturing_year: 2022,
  passenger_capacity: 7,
  luggage_capacity: 4,
  transmission: 'AUTOMATIC',
  with_driver: true,
  license_plate: '',
  base_price: 850000,
  pricing_type: 'PER_VEHICLE',
  photos: [],
});

const autoTranslateAll = async () => {
  if (!form.name && !form.short_description) {
    alert('Harap isi Judul Unit atau Ringkasan Singkat (Bahasa Indonesia) terlebih dahulu.');
    return;
  }

  isTranslating.value = true;
  try {
    const res = await axios.post('/api/translate-batch', {
      fields: {
        name: form.name,
        short_description: form.short_description,
        full_description: form.full_description,
      },
      source: 'id',
      target: 'en',
    });

    if (res.data && res.data.translations) {
      const t = res.data.translations;
      form.name_en = t.name || '';
      form.short_description_en = t.short_description || '';
      form.full_description_en = t.full_description || '';
      contentLang.value = 'en';
    }
  } catch (err) {
    console.error('Translation error:', err);
  } finally {
    isTranslating.value = false;
  }
};

const handlePhotoUpload = (e) => {
  form.photos = Array.from(e.target.files);
};

const submit = () => {
  form.post(route('partner.vehicles.store'));
};
</script>

<template>
  <PartnerLayout>
    <Head title="Daftarkan Unit Rental — Mitra Vendor" />

    <div class="p-6 max-w-3xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-bold text-accent uppercase tracking-wider">Katalog Armada</span>
          <h1 class="text-2xl font-black text-text-main">Tambah Armada Rental Kendaraan</h1>
          <p class="text-xs text-text-muted mt-0.5">Daftarkan mobil, minibus, atau kapal wisata untuk disewa wisatawan.</p>
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
              <span>🇮🇩</span> Indonesia <span class="text-[10px] bg-primary/10 text-primary px-1.5 py-0.2 rounded font-semibold">Default</span>
            </button>
            <button
              type="button"
              @click="contentLang = 'en'"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5',
                contentLang === 'en' ? 'bg-white shadow text-secondary font-black' : 'text-text-muted hover:text-text-main'
              ]"
            >
              <span>🇬🇧</span> English <span v-if="form.name_en" class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.2 rounded font-semibold">Ready</span>
            </button>
          </div>
        </div>

        <button
          type="button"
          @click="autoTranslateAll"
          :disabled="isTranslating"
          class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-teal-600 to-secondary hover:from-teal-700 hover:to-secondary/90 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition disabled:opacity-50"
        >
          <span v-if="isTranslating" class="animate-spin text-sm">⏳</span>
          <span v-else>✨</span>
          <span>{{ isTranslating ? 'Menerjemahkan via AI...' : 'Auto-Translate ID ➔ EN' }}</span>
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <h2 class="text-sm font-bold text-text-main uppercase tracking-wider border-b border-border pb-2">
            1. Spesifikasi Teknis Armada
          </h2>

          <div class="space-y-4 text-xs">
            <div v-show="contentLang === 'id'">
              <label class="block font-bold text-text-muted uppercase mb-1">Judul Tampilan Unit (Bahasa Indonesia) *</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Toyota Innova Reborn Diesel + Driver Berpengalaman"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
              <span v-if="form.errors.name" class="text-danger mt-1 block">{{ form.errors.name }}</span>
            </div>
            <div v-show="contentLang === 'en'">
              <label class="block font-bold text-secondary uppercase mb-1">Unit Display Title (English)</label>
              <input
                v-model="form.name_en"
                type="text"
                placeholder="e.g. Toyota Innova Reborn Diesel + Experienced English Driver"
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary focus:ring-secondary bg-secondary/5"
              />
              <p class="text-[11px] text-text-muted mt-1">Jika dikosongkan, sistem akan otomatis menerjemahkan dari teks Indonesia.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Jenis Kendaraan *</label>
                <select
                  v-model="form.vehicle_type"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                >
                  <option v-for="v in vehicleTypes" :key="v.value" :value="v.value">{{ v.label }}</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Merek *</label>
                <input
                  v-model="form.brand"
                  type="text"
                  placeholder="Toyota / Daihatsu / Isuzu"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Model / Varian *</label>
                <input
                  v-model="form.model"
                  type="text"
                  placeholder="Innova / Avanza / HiAce"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Tahun Perakitan *</label>
                <input
                  v-model.number="form.manufacturing_year"
                  type="number"
                  min="2005"
                  max="2030"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Kapasitas Kursi *</label>
                <input
                  v-model.number="form.passenger_capacity"
                  type="number"
                  min="1"
                  max="60"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Kapasitas Koper</label>
                <input
                  v-model.number="form.luggage_capacity"
                  type="number"
                  min="0"
                  max="50"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                  required
                />
              </div>

              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Transmisi *</label>
                <select
                  v-model="form.transmission"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                >
                  <option v-for="tr in transmissions" :key="tr.value" :value="tr.value">{{ tr.label }}</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-bold text-text-muted uppercase mb-1">Nomor Plat Polisi *</label>
                <input
                  v-model="form.license_plate"
                  type="text"
                  placeholder="Contoh: BK 1842 TO"
                  class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary uppercase"
                  required
                />
              </div>

              <div class="flex items-center pt-5">
                <input
                  v-model="form.with_driver"
                  id="driver_toggle"
                  type="checkbox"
                  class="rounded text-primary focus:ring-primary w-4 h-4 mr-2"
                />
                <label for="driver_toggle" class="cursor-pointer font-bold text-text-main text-xs">
                  Sewa Termasuk Pengemudi (Driver)
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-surface p-6 rounded-2xl border border-border shadow-card space-y-4">
          <div class="flex items-center justify-between border-b border-border pb-2">
            <h2 class="text-sm font-bold text-text-main uppercase tracking-wider">
              2. Tarif & Deskripsi Layanan
            </h2>
            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full" :class="contentLang === 'id' ? 'bg-primary/10 text-primary' : 'bg-secondary/10 text-secondary'">
              Mode: {{ contentLang === 'id' ? '🇮🇩 Bahasa Indonesia' : '🇬🇧 English Version' }}
            </span>
          </div>

          <div class="space-y-4 text-xs">
            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Tarif Sewa Harian (IDR) *</label>
              <input
                v-model.number="form.base_price"
                type="number"
                step="10000"
                min="50000"
                placeholder="850000"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
            </div>

            <!-- Short Description -->
            <div v-show="contentLang === 'id'">
              <label class="block font-bold text-text-muted uppercase mb-1">Ringkasan Singkat (ID) *</label>
              <input
                v-model="form.short_description"
                type="text"
                placeholder="Pilihan armada prima untuk kenyamanan keluarga di rute Danau Toba"
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              />
            </div>
            <div v-show="contentLang === 'en'">
              <label class="block font-bold text-secondary uppercase mb-1">Short Summary (EN)</label>
              <input
                v-model="form.short_description_en"
                type="text"
                placeholder="Premium vehicle option for family comfort touring Lake Toba"
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary bg-secondary/5"
              />
            </div>

            <!-- Full Description -->
            <div v-show="contentLang === 'id'">
              <label class="block font-bold text-text-muted uppercase mb-1">Deskripsi Kondisi Armada & Kebijakan BBM (ID) *</label>
              <textarea
                v-model="form.full_description"
                rows="4"
                placeholder="Armada ber-AC dingin, bersih, terawat berkala. Sudah termasuk BBM dan uang makan driver untuk pemakaian dalam kawasan kaldera Danau Toba..."
                class="w-full text-xs font-medium rounded-xl border-border focus:border-primary focus:ring-primary"
                required
              ></textarea>
            </div>
            <div v-show="contentLang === 'en'">
              <label class="block font-bold text-secondary uppercase mb-1">Fleet Condition Description & Fuel Policy (EN)</label>
              <textarea
                v-model="form.full_description_en"
                rows="4"
                placeholder="Cold AC fleet, clean, periodically serviced. Includes fuel and driver meals for Lake Toba caldera area..."
                class="w-full text-xs font-medium rounded-xl border-secondary/30 focus:border-secondary bg-secondary/5"
              ></textarea>
            </div>

            <div>
              <label class="block font-bold text-text-muted uppercase mb-1">Upload Foto Armada (JPG/PNG, Max 5MB)</label>
              <input
                type="file"
                @change="handlePhotoUpload"
                multiple
                accept="image/*"
                class="w-full text-xs text-text-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-secondary/90 cursor-pointer"
              />
            </div>
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
            class="px-8 py-3 bg-secondary hover:bg-secondary/90 text-white rounded-xl text-xs font-bold transition shadow-lg disabled:opacity-50"
          >
            {{ form.processing ? 'Menyimpan...' : 'Simpan & Ajukan Verifikasi Admin' }}
          </button>
        </div>
      </form>
    </div>
  </PartnerLayout>
</template>

