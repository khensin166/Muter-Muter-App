<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
  business_name: '',
  brand_name: '',
  city: 'Samosir',
  address: '',
  pic_name: '',
  pic_phone: '',
  email: '',
  password: '',
  password_confirmation: '',
  bank_name: 'Bank Mandiri',
  bank_account_number: '',
  bank_account_holder: '',
  ktp_file: null,
  nib_file: null,
  fleet_photos: [],
});

const handleFile = (field, event) => {
  if (field === 'fleet_photos') {
    form.fleet_photos = Array.from(event.target.files);
  } else {
    form[field] = event.target.files[0];
  }
};

const submit = () => {
  form.post(route('partner.register.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <AppLayout>
    <Head title="Pendaftaran Mitra Vendor — BetaTudia Sumatera Utara" />

    <div class="py-12 bg-background min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-10">
          <span class="text-xs font-bold text-accent uppercase tracking-widest bg-accent/10 px-3 py-1 rounded-full">
            Mitra Vendor Danau Toba
          </span>
          <h1 class="text-3xl font-black text-text-main mt-3">
            Daftarkan Usaha Wisata atau Armada Anda
          </h1>
          <p class="text-sm text-text-muted mt-2 max-w-xl mx-auto">
            Bergabunglah dengan ekosistem terintegrasi Muter-Muter Danau Toba. Jangkau ribuan wisatawan domestik dengan sistem manajemen pesanan terpusat.
          </p>
        </div>

        <!-- Form Card -->
        <div class="bg-surface rounded-3xl border border-border p-6 sm:p-10 shadow-card">
          <form @submit.prevent="submit" class="space-y-8">
            <!-- Section 1: Profil Usaha -->
            <div>
              <h2 class="text-base font-bold text-text-main border-b border-border pb-2 flex items-center gap-2">
                <span>🏢</span>
                <span>1. Profil Usaha / Operator</span>
              </h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Nama Badan Usaha / PT / CV <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="form.business_name"
                    type="text"
                    required
                    placeholder="Contoh: CV Horas Samosir Wisata"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                  <div v-if="form.errors.business_name" class="text-xs text-danger mt-1">{{ form.errors.business_name }}</div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Nama Merek Komersial (Opsional)
                  </label>
                  <input
                    v-model="form.brand_name"
                    type="text"
                    placeholder="Contoh: Horas Samosir Tour"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                </div>

                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Wilayah Operasional Utama <span class="text-danger">*</span>
                  </label>
                  <select
                    v-model="form.city"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  >
                    <option value="Samosir">Samosir (Tomok, Ambarita, Pangururan)</option>
                    <option value="Simalungun / Parapat">Simalungun / Parapat</option>
                    <option value="Karo / Merek">Karo / Merek / Berastagi</option>
                    <option value="Toba / Balige">Toba / Balige</option>
                    <option value="Medan">Medan Hub</option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Alamat Kantor / Garasi
                  </label>
                  <input
                    v-model="form.address"
                    type="text"
                    placeholder="Jl. Lintas Tomok No. 18"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                </div>
              </div>
            </div>

            <!-- Section 2: Penanggung Jawab & Akun Login -->
            <div>
              <h2 class="text-base font-bold text-text-main border-b border-border pb-2 flex items-center gap-2">
                <span>👤</span>
                <span>2. Penanggung Jawab & Akun Pemilik (Owner)</span>
              </h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Nama Lengkap PIC (Sesuai KTP) <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="form.pic_name"
                    type="text"
                    required
                    placeholder="Togu Simanjuntak"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                  <div v-if="form.errors.pic_name" class="text-xs text-danger mt-1">{{ form.errors.pic_name }}</div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Nomor WhatsApp PIC <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="form.pic_phone"
                    type="text"
                    required
                    placeholder="08126000xxxx"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                  <span class="text-[11px] text-text-muted">Untuk notifikasi pesanan masuk & SLA 24 jam</span>
                  <div v-if="form.errors.pic_phone" class="text-xs text-danger mt-1">{{ form.errors.pic_phone }}</div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Email Login <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="kontak@mitra.com"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                  <div v-if="form.errors.email" class="text-xs text-danger mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                      Password <span class="text-danger">*</span>
                    </label>
                    <input
                      v-model="form.password"
                      type="password"
                      required
                      class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                      Konfirmasi Password <span class="text-danger">*</span>
                    </label>
                    <input
                      v-model="form.password_confirmation"
                      type="password"
                      required
                      class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 3: Rekening Pencairan Dana (Settlement) -->
            <div>
              <h2 class="text-base font-bold text-text-main border-b border-border pb-2 flex items-center gap-2">
                <span>💳</span>
                <span>3. Rekening Bank Pencairan Dana (Settlement)</span>
              </h2>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Bank Penerima <span class="text-danger">*</span>
                  </label>
                  <select
                    v-model="form.bank_name"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  >
                    <option value="Bank Mandiri">Bank Mandiri</option>
                    <option value="BCA">BCA</option>
                    <option value="BRI">BRI</option>
                    <option value="BNI">BNI</option>
                    <option value="Bank Sumut">Bank Sumut</option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Nomor Rekening <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="form.bank_account_number"
                    type="text"
                    required
                    placeholder="1070019xxxx"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                  <div v-if="form.errors.bank_account_number" class="text-xs text-danger mt-1">{{ form.errors.bank_account_number }}</div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-1">
                    Nama Pemilik Rekening <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="form.bank_account_holder"
                    type="text"
                    required
                    placeholder="Sesuai buku tabungan"
                    class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
                  />
                </div>
              </div>
            </div>

            <!-- Section 4: Unggah Dokumen Verifikasi (KYC) -->
            <div>
              <h2 class="text-base font-bold text-text-main border-b border-border pb-2 flex items-center gap-2">
                <span>📁</span>
                <span>4. Dokumen Verifikasi Wajib (KYC)</span>
              </h2>
              <p class="text-xs text-text-muted mt-1">Format file: JPG, PNG, atau PDF (Maksimal 5MB per dokumen).</p>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <!-- KTP -->
                <div class="p-4 rounded-2xl border border-dashed border-border bg-background flex flex-col justify-between">
                  <div>
                    <label class="block text-xs font-bold text-text-main mb-1">KTP Penanggung Jawab <span class="text-danger">*</span></label>
                    <span class="text-[11px] text-text-muted block mb-3">Foto KTP asli terlihat jelas</span>
                  </div>
                  <input
                    type="file"
                    required
                    accept="image/*,.pdf"
                    @change="handleFile('ktp_file', $event)"
                    class="text-xs text-text-muted file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark"
                  />
                  <div v-if="form.errors.ktp_file" class="text-xs text-danger mt-1">{{ form.errors.ktp_file }}</div>
                </div>

                <!-- NIB -->
                <div class="p-4 rounded-2xl border border-dashed border-border bg-background flex flex-col justify-between">
                  <div>
                    <label class="block text-xs font-bold text-text-main mb-1">NIB / Izin Usaha / SKU <span class="text-danger">*</span></label>
                    <span class="text-[11px] text-text-muted block mb-3">Surat Izin Operasional / SKU Desa</span>
                  </div>
                  <input
                    type="file"
                    required
                    accept="image/*,.pdf"
                    @change="handleFile('nib_file', $event)"
                    class="text-xs text-text-muted file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark"
                  />
                  <div v-if="form.errors.nib_file" class="text-xs text-danger mt-1">{{ form.errors.nib_file }}</div>
                </div>

                <!-- Foto Armada -->
                <div class="p-4 rounded-2xl border border-dashed border-border bg-background flex flex-col justify-between">
                  <div>
                    <label class="block text-xs font-bold text-text-main mb-1">Foto Unit / Armada <span class="text-danger">*</span></label>
                    <span class="text-[11px] text-text-muted block mb-3">Foto kendaraan atau kantor operasional</span>
                  </div>
                  <input
                    type="file"
                    required
                    multiple
                    accept="image/*"
                    @change="handleFile('fleet_photos', $event)"
                    class="text-xs text-text-muted file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark"
                  />
                  <div v-if="form.errors.fleet_photos" class="text-xs text-danger mt-1">{{ form.errors.fleet_photos }}</div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
              <span class="text-xs text-text-muted">
                Sudah punya akun mitra? <Link :href="route('login')" class="text-primary font-bold hover:underline">Masuk di sini</Link>
              </span>
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full sm:w-auto bg-accent hover:bg-accent-hover text-white text-sm font-bold py-3 px-8 rounded-xl shadow-md transition disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <span v-if="form.processing">Memproses Pendaftaran...</span>
                <span v-else>Kirim Pendaftaran Mitra →</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

