<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';

const props = defineProps({
  partner: Object,
  staffMembers: Array,
});

const isAddModalOpen = ref(false);

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
});

const submitAddStaff = () => {
  form.post(route('partner.staff.store'), {
    onSuccess: () => {
      isAddModalOpen.value = false;
      form.reset();
    },
  });
};
</script>

<template>
  <PartnerLayout>
    <Head :title="`Manajemen Staf — ${partner.name}`" />

    <!-- Header Section -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <Link :href="route('partner.dashboard')" class="text-xs text-primary font-bold hover:underline">
            ← Kembali ke Dashboard
          </Link>
        </div>
        <h1 class="text-2xl font-black text-text-main mt-1">Manajemen Staf & Hak Akses</h1>
        <p class="text-xs text-text-muted mt-0.5">
          Kelola anggota tim operasional untuk memverifikasi ketersediaan pesanan (SLA 24h) dan mengelola jadwal harian armada.
        </p>
      </div>

      <button
        @click="isAddModalOpen = true"
        class="bg-accent hover:bg-accent-hover text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-xs transition flex items-center justify-center gap-2 shrink-0"
      >
        <span>+</span>
        <span>Tambah Staf Operasional</span>
      </button>
    </div>

    <!-- Staff Table -->
    <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
      <div class="p-6 border-b border-border flex items-center justify-between">
        <h2 class="text-base font-bold text-text-main">Daftar Akun Tim Mitra ({{ staffMembers.length }})</h2>
        <span class="text-xs text-text-muted">Multi-Staff Enabled</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-background text-text-muted uppercase tracking-wider text-[11px] border-b border-border">
            <tr>
              <th class="p-4 pl-6">Nama & Kontak</th>
              <th class="p-4">Email Login</th>
              <th class="p-4">Tingkat Hak Akses</th>
              <th class="p-4">Status Akun</th>
              <th class="p-4">Bergabung</th>
              <th class="p-4 pr-6 text-right">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-for="member in staffMembers" :key="member.id" class="hover:bg-surface-muted/50 transition">
              <td class="p-4 pl-6">
                <span class="font-bold text-text-main block text-sm">{{ member.name }}</span>
                <span class="text-text-muted text-[11px]">{{ member.phone }}</span>
              </td>
              <td class="p-4 font-medium text-text-main">
                {{ member.email }}
              </td>
              <td class="p-4">
                <span
                  :class="[
                    'px-2.5 py-1 rounded-full text-xs font-extrabold tracking-wide uppercase',
                    member.role === 'OWNER' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-sky-100 text-sky-800 border border-sky-200'
                  ]"
                >
                  {{ member.role === 'OWNER' ? '👑 Owner (Pemilik)' : '🛠️ Staf Operasional' }}
                </span>
              </td>
              <td class="p-4">
                <span
                  :class="[
                    'px-2 py-0.5 rounded text-[11px] font-bold',
                    member.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
                  ]"
                >
                  {{ member.is_active ? '● Aktif' : '○ Nonaktif' }}
                </span>
              </td>
              <td class="p-4 text-text-muted">
                {{ member.joined_at }}
              </td>
              <td class="p-4 pr-6 text-right">
                <div v-if="member.role !== 'OWNER'" class="flex items-center justify-end gap-2">
                  <Link
                    :href="route('partner.staff.toggle', member.id)"
                    method="patch"
                    as="button"
                    class="text-xs font-semibold px-2.5 py-1 rounded-lg border border-border hover:bg-surface-muted transition"
                  >
                    {{ member.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </Link>
                  <Link
                    :href="route('partner.staff.destroy', member.id)"
                    method="delete"
                    as="button"
                    class="text-xs font-semibold px-2.5 py-1 rounded-lg border border-rose-200 text-danger hover:bg-rose-50 transition"
                  >
                    Hapus
                  </Link>
                </div>
                <span v-else class="text-[11px] text-text-muted italic">Akun Utama</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Tambah Staf -->
    <div
      v-if="isAddModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
    >
      <div class="bg-surface rounded-3xl border border-border p-6 max-w-md w-full shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-border">
          <h3 class="text-base font-bold text-text-main">Tambah Staf Operasional</h3>
          <button @click="isAddModalOpen = false" class="text-text-muted hover:text-text-main">✕</button>
        </div>

        <form @submit.prevent="submitAddStaff" class="space-y-4 mt-4">
          <div>
            <label class="block text-xs font-bold text-text-muted uppercase mb-1">Nama Lengkap Staf *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Contoh: Butet Nainggolan"
              class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
            />
            <div v-if="form.errors.name" class="text-xs text-danger mt-1">{{ form.errors.name }}</div>
          </div>

          <div>
            <label class="block text-xs font-bold text-text-muted uppercase mb-1">Email Login *</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="staf@horassamosir.com"
              class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
            />
            <div v-if="form.errors.email" class="text-xs text-danger mt-1">{{ form.errors.email }}</div>
          </div>

          <div>
            <label class="block text-xs font-bold text-text-muted uppercase mb-1">Nomor WhatsApp *</label>
            <input
              v-model="form.phone"
              type="text"
              required
              placeholder="08126000xxxx"
              class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
            />
            <div v-if="form.errors.phone" class="text-xs text-danger mt-1">{{ form.errors.phone }}</div>
          </div>

          <div>
            <label class="block text-xs font-bold text-text-muted uppercase mb-1">Password Sementara *</label>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="Minimal 8 karakter"
              class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
            />
            <div v-if="form.errors.password" class="text-xs text-danger mt-1">{{ form.errors.password }}</div>
          </div>

          <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
            <button
              type="button"
              @click="isAddModalOpen = false"
              class="text-xs font-semibold px-4 py-2 rounded-xl border border-border text-text-muted hover:bg-surface-muted"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="bg-accent hover:bg-accent-hover text-white text-xs font-bold px-4 py-2 rounded-xl transition"
            >
              Simpan Staf
            </button>
          </div>
        </form>
      </div>
    </div>
  </PartnerLayout>
</template>

