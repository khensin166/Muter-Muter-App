<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  partner: Object,
  documents: Array,
  members: Array,
});

const isRejectModalOpen = ref(false);
const rejectForm = useForm({
  reason: '',
});

const submitReject = () => {
  rejectForm.post(route('admin.partners.reject', props.partner.id), {
    onSuccess: () => {
      isRejectModalOpen.value = false;
    },
  });
};
</script>

<template>
  <AdminLayout>
    <Head :title="`Verifikasi Mitra — ${partner.business_name}`" />

    <!-- Back Button & Breadcrumbs -->
    <div class="mb-6 flex items-center justify-between">
      <Link :href="route('admin.partners.index')" class="text-xs font-bold text-primary hover:underline">
        ← Kembali ke Daftar Mitra
      </Link>
      <span
        :class="[
          'px-3 py-1 rounded-full text-xs font-black uppercase',
          partner.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' :
          partner.status === 'PENDING' ? 'bg-amber-100 text-amber-800' :
          partner.status === 'REJECTED' ? 'bg-rose-100 text-danger' :
          'bg-slate-100 text-slate-700'
        ]"
      >
        Status: {{ partner.status }}
      </span>
    </div>

    <!-- Header Card -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-text-main">{{ partner.business_name }}</h1>
        <p v-if="partner.brand_name" class="text-xs text-text-muted mt-0.5">
          Nama Merek: <strong class="text-text-main">{{ partner.brand_name }}</strong>
        </p>
        <p class="text-xs text-text-muted mt-1">
          📍 {{ partner.city }} • Alamat: {{ partner.address || 'Tidak ada alamat' }}
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-3">
        <template v-if="partner.status === 'PENDING'">
          <Link
            :href="route('admin.partners.approve', partner.id)"
            method="post"
            as="button"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 px-5 rounded-xl shadow-xs transition"
          >
            ✓ Setujui Mitra (Approve)
          </Link>
          <button
            @click="isRejectModalOpen = true"
            class="bg-surface border border-rose-200 text-danger hover:bg-rose-50 text-xs font-bold py-2.5 px-4 rounded-xl transition"
          >
            ✕ Tolak Pendaftaran
          </button>
        </template>
        <template v-else-if="partner.status === 'APPROVED'">
          <Link
            :href="route('admin.partners.suspend', partner.id)"
            method="post"
            as="button"
            class="border border-border text-text-muted hover:text-danger hover:border-danger text-xs font-bold py-2 px-4 rounded-xl transition"
          >
            Suspend Mitra
          </Link>
        </template>
      </div>
    </div>

    <!-- Two-column info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
      <!-- PIC Info -->
      <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs">
        <h2 class="text-base font-bold text-text-main border-b border-border pb-2 flex items-center gap-2">
          <span>👤</span>
          <span>Penanggung Jawab (PIC)</span>
        </h2>
        <div class="mt-4 space-y-2 text-xs">
          <div class="flex justify-between py-1 border-b border-border/50">
            <span class="text-text-muted">Nama Lengkap PIC:</span>
            <span class="font-bold text-text-main">{{ partner.pic_name }}</span>
          </div>
          <div class="flex justify-between py-1 border-b border-border/50">
            <span class="text-text-muted">Nomor WhatsApp:</span>
            <a :href="`https://wa.me/${partner.pic_phone}`" target="_blank" class="font-bold text-primary hover:underline">
              {{ partner.pic_phone }} ↗
            </a>
          </div>
          <div class="flex justify-between py-1 border-b border-border/50">
            <span class="text-text-muted">Email Kontak:</span>
            <span class="font-bold text-text-main">{{ partner.email }}</span>
          </div>
          <div class="flex justify-between py-1">
            <span class="text-text-muted">Tanggal Registrasi:</span>
            <span class="text-text-main">{{ partner.registered_at }}</span>
          </div>
        </div>
      </div>

      <!-- Bank Settlement Info -->
      <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs">
        <h2 class="text-base font-bold text-text-main border-b border-border pb-2 flex items-center gap-2">
          <span>💳</span>
          <span>Rekening Pencairan Dana (Settlement)</span>
        </h2>
        <div class="mt-4 space-y-2 text-xs">
          <div class="flex justify-between py-1 border-b border-border/50">
            <span class="text-text-muted">Nama Bank:</span>
            <span class="font-bold text-text-main">{{ partner.bank_name || '-' }}</span>
          </div>
          <div class="flex justify-between py-1 border-b border-border/50">
            <span class="text-text-muted">Nomor Rekening:</span>
            <span class="font-bold text-text-main font-mono text-sm">{{ partner.bank_account_number || '-' }}</span>
          </div>
          <div class="flex justify-between py-1">
            <span class="text-text-muted">Atas Nama Pemilik:</span>
            <span class="font-bold text-text-main">{{ partner.bank_account_holder || '-' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Documents Section (KYC) -->
    <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs mb-8">
      <h2 class="text-base font-bold text-text-main border-b border-border pb-2 flex items-center gap-2">
        <span>📁</span>
        <span>Dokumen Legalitas & Verifikasi KYC ({{ documents.length }})</span>
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
        <div
          v-for="doc in documents"
          :key="doc.id"
          class="border border-border rounded-xl p-4 bg-background flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-primary uppercase">{{ doc.type }}</span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-surface border border-border text-text-muted">
                {{ doc.status }}
              </span>
            </div>
            <p v-if="doc.rejection_reason" class="text-xs text-danger mb-2">
              Alasan tolak: {{ doc.rejection_reason }}
            </p>
          </div>

          <div class="mt-4 pt-3 border-t border-border flex items-center justify-between">
            <a
              :href="doc.file_path"
              target="_blank"
              class="text-xs font-bold text-primary hover:underline flex items-center gap-1"
            >
              <span>🖼️</span>
              <span>Buka Dokumen</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tolak Pendaftaran -->
    <div
      v-if="isRejectModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
    >
      <div class="bg-surface rounded-3xl border border-border p-6 max-w-md w-full shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-border">
          <h3 class="text-base font-bold text-danger">Tolak Pendaftaran Mitra</h3>
          <button @click="isRejectModalOpen = false" class="text-text-muted hover:text-text-main">✕</button>
        </div>

        <form @submit.prevent="submitReject" class="space-y-4 mt-4">
          <div>
            <label class="block text-xs font-bold text-text-muted uppercase mb-1">
              Alasan Penolakan (Wajib) *
            </label>
            <textarea
              v-model="rejectForm.reason"
              required
              rows="3"
              placeholder="Contoh: Foto KTP buram dan dokumen NIB tidak sesuai dengan nama penanggung jawab."
              class="w-full text-sm border-border rounded-xl focus:border-primary focus:ring-primary"
            ></textarea>
            <div v-if="rejectForm.errors.reason" class="text-xs text-danger mt-1">{{ rejectForm.errors.reason }}</div>
          </div>

          <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
            <button
              type="button"
              @click="isRejectModalOpen = false"
              class="text-xs font-semibold px-4 py-2 rounded-xl border border-border text-text-muted hover:bg-surface-muted"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="rejectForm.processing"
              class="bg-danger hover:bg-rose-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition"
            >
              Konfirmasi Tolak
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

