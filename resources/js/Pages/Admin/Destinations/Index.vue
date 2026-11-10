<script setup>
import { formatRupiah } from '@/Composables/useFormat';
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  destinations: {
    type: Array,
    default: () => [],
  },
  clusters: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
  currentCluster: {
    type: String,
    default: '',
  },
});

// Modals state
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedDestForDelete = ref(null);

// Create Form
const createForm = useForm({
  name: '',
  cluster: props.clusters[0]?.value || 'PARAPAT',
  category_id: props.categories[0]?.id || '',
  description: '',
  ticket_price_domestic: 15000,
  ticket_price_foreign: 50000,
  operating_hours: '07:00 - 18:00 WIB',
  latitude: 2.6540,
  longitude: 98.8615,
  is_featured: true,
  image_urls: [''],
  photos: [],
});

const createPhotoPreviews = ref([]);

// Edit Form
const editForm = useForm({
  id: '',
  name: '',
  cluster: '',
  category_id: '',
  description: '',
  ticket_price_domestic: 0,
  ticket_price_foreign: 0,
  operating_hours: '',
  latitude: 2.6540,
  longitude: 98.8615,
  is_featured: true,
  is_active: true,
  image_urls: [''],
  photos: [],
  deleted_media_ids: [],
  primary_media_id: null,
});

const editExistingMedia = ref([]);
const editPhotoPreviews = ref([]);

// Methods for Create Form
const addCreateImageUrlField = () => {
  createForm.image_urls.push('');
};

const removeCreateImageUrlField = (idx) => {
  createForm.image_urls.splice(idx, 1);
};

const handleCreateFileChange = (e) => {
  const newFiles = Array.from(e.target.files || []);
  if (!newFiles.length) return;
  createForm.photos = [...createForm.photos, ...newFiles];
  const newUrls = newFiles.map((file) => ({ name: file.name, url: URL.createObjectURL(file) }));
  createPhotoPreviews.value = [...createPhotoPreviews.value, ...newUrls];
  e.target.value = '';
};

const removeCreatePendingPhoto = (idx) => {
  createForm.photos.splice(idx, 1);
  createPhotoPreviews.value.splice(idx, 1);
};

const submitCreate = () => {
  createForm.post(route('admin.destinations.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
      createPhotoPreviews.value = [];
    },
  });
};

// Methods for Edit Form
const openEditModal = (dest) => {
  editForm.id = dest.id;
  editForm.name = dest.name;
  editForm.cluster = dest.cluster;
  editForm.category_id = dest.category_id || props.categories[0]?.id || '';
  editForm.description = dest.description || dest.short_description || '';
  editForm.ticket_price_domestic = dest.ticket_price_domestic || 0;
  editForm.ticket_price_foreign = dest.ticket_price_foreign || 0;
  editForm.operating_hours = dest.operating_hours || '07:00 - 18:00 WIB';
  editForm.latitude = dest.latitude || 2.6540;
  editForm.longitude = dest.longitude || 98.8615;
  editForm.is_featured = dest.is_featured ?? true;
  editForm.is_active = dest.is_active ?? true;
  editForm.image_urls = [''];
  editForm.photos = [];
  editForm.deleted_media_ids = [];

  editExistingMedia.value = (dest.media_gallery || []).map((m) => ({
    ...m,
  }));

  const primary = dest.media_gallery?.find((m) => m.is_primary) || dest.media_gallery?.[0];
  editForm.primary_media_id = primary?.id || null;
  editPhotoPreviews.value = [];

  showEditModal.value = true;
};

const addEditImageUrlField = () => {
  editForm.image_urls.push('');
};

const removeEditImageUrlField = (idx) => {
  editForm.image_urls.splice(idx, 1);
};

const handleEditFileChange = (e) => {
  const newFiles = Array.from(e.target.files || []);
  if (!newFiles.length) return;
  editForm.photos = [...editForm.photos, ...newFiles];
  const newUrls = newFiles.map((file) => ({ name: file.name, url: URL.createObjectURL(file) }));
  editPhotoPreviews.value = [...editPhotoPreviews.value, ...newUrls];
  e.target.value = '';
};

const removeEditPendingPhoto = (idx) => {
  editForm.photos.splice(idx, 1);
  editPhotoPreviews.value.splice(idx, 1);
};

const toggleDeleteMedia = (mediaId) => {
  const index = editForm.deleted_media_ids.indexOf(mediaId);
  if (index > -1) {
    editForm.deleted_media_ids.splice(index, 1);
  } else {
    editForm.deleted_media_ids.push(mediaId);
    if (editForm.primary_media_id === mediaId) {
      editForm.primary_media_id = null;
    }
  }
};

const submitUpdate = () => {
  editForm.post(route('admin.destinations.update', editForm.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
      editPhotoPreviews.value = [];
    },
  });
};

// Delete Handlers
const openDeleteModal = (dest) => {
  selectedDestForDelete.value = dest;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!selectedDestForDelete.value) return;
  router.delete(route('admin.destinations.destroy', selectedDestForDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedDestForDelete.value = null;
    },
  });
};

// 1-Click Status Toggle
const toggleStatus = (dest) => {
  router.patch(route('admin.destinations.toggle', dest.id), {}, {
    preserveScroll: true,
  });
};

const filterByCluster = (clusterValue) => {
  router.get(route('admin.destinations.index'), clusterValue ? { cluster: clusterValue } : {}, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Katalog Destinasi Wisata Master — Admin Console" />

    <!-- Header -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 text-[11px] font-bold text-primary uppercase tracking-wider mb-1">
          <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
          <span>Master Data & Galeri Destinasi</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-text-main tracking-tight">
          Manajemen Destinasi Wisata Sumatera Utara
        </h1>
        <p class="text-xs text-text-muted mt-1">
          Katalog induk titik objek wisata yang otomatis terhubung ke rute paket wisata dan peta interaktif.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <Link
          href="/admin/destination-clusters"
          class="px-3.5 py-2.5 rounded-xl border border-border bg-surface hover:bg-surface-muted text-text-main text-xs font-bold transition inline-flex items-center gap-1.5"
        >
          <span>🗺️ Kelola Klaster Wilayah</span>
        </Link>
        <Link
          href="/admin/tours/create"
          class="px-3.5 py-2.5 rounded-xl border border-border bg-surface hover:bg-surface-muted text-text-main text-xs font-bold transition inline-flex items-center gap-1.5"
        >
          <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>Buat Paket Wisata</span>
        </Link>
        <button
          type="button"
          @click="showCreateModal = true"
          class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>Tambah Destinasi Baru</span>
        </button>
      </div>
    </div>

    <!-- Cluster Filter Pills -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
      <button
        type="button"
        @click="filterByCluster('')"
        :class="!currentCluster ? 'bg-primary-dark text-white' : 'bg-surface text-text-muted border border-border hover:bg-surface-muted'"
        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
      >
        Semua Klaster ({{ destinations.length }})
      </button>
      <button
        v-for="c in clusters"
        :key="c.value"
        type="button"
        @click="filterByCluster(c.value)"
        :class="currentCluster === c.value ? 'bg-primary text-white' : 'bg-surface text-text-muted border border-border hover:bg-surface-muted'"
        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
      >
        {{ c.label }}
      </button>
    </div>

    <!-- Destinations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="dest in destinations"
        :key="dest.id"
        class="bg-surface rounded-2xl border border-border overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col justify-between"
      >
        <div>
          <!-- Thumbnail & Badges -->
          <div class="relative h-44 bg-surface-muted overflow-hidden group">
            <img :src="dest.image_url" :alt="dest.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />

            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
              <span class="px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">
                {{ dest.cluster_label }}
              </span>
              <button
                type="button"
                @click="toggleStatus(dest)"
                :title="dest.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'"
                :class="dest.is_active ? 'bg-emerald-600 text-white' : 'bg-slate-700/90 text-slate-200'"
                class="px-2.5 py-1 rounded-lg backdrop-blur-xs text-[10px] font-bold cursor-pointer transition shadow-xs flex items-center gap-1"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="dest.is_active ? 'bg-white animate-pulse' : 'bg-slate-400'"></span>
                <span>{{ dest.is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </button>
            </div>

            <div class="absolute bottom-3 right-3 px-2 py-0.5 rounded-md bg-surface/90 backdrop-blur-xs text-text-main text-[10px] font-bold shadow-2xs">
              {{ dest.media_count }} Foto Galeri
            </div>
          </div>

          <!-- Body Info -->
          <div class="p-5">
            <div class="flex items-center justify-between gap-2 mb-1">
              <span class="text-[10px] font-bold uppercase tracking-wider text-primary">{{ dest.category_name }}</span>
              <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                :class="dest.stops_usage_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-surface-muted text-text-muted border border-border'"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="dest.stops_usage_count > 0 ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                Terikat di {{ dest.stops_usage_count }} Rute
              </span>
            </div>

            <h3 class="text-base font-extrabold text-text-main line-clamp-1">{{ dest.name }}</h3>

            <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
              {{ dest.short_description || dest.description || 'Tidak ada deskripsi.' }}
            </p>

            <div class="mt-3 flex items-center gap-3 text-[11px] text-text-muted">
              <span class="flex items-center gap-1">
                <span>🕒</span>
                <span>{{ dest.operating_hours || '07:00 - 18:00 WIB' }}</span>
              </span>
              <span class="text-border">&bull;</span>
              <span class="flex items-center gap-1 font-mono text-[10px]">
                <span>📍</span>
                <span>{{ Number(dest.latitude).toFixed(4) }}, {{ Number(dest.longitude).toFixed(4) }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="px-5 py-3.5 bg-surface-muted/80 border-t border-border flex items-center justify-between text-xs">
          <div class="tabular-nums">
            <span class="text-[10px] text-text-muted block">Tiket Domestik</span>
            <span class="font-bold text-text-main">
              {{ dest.ticket_price_domestic > 0 ? formatRupiah(dest.ticket_price_domestic) : 'Gratis' }}
            </span>
          </div>

          <div class="flex items-center gap-1.5">
            <button
              type="button"
              @click="openEditModal(dest)"
              class="px-2.5 py-1.5 rounded-lg border border-border bg-surface hover:bg-surface-muted text-text-main text-xs font-bold transition cursor-pointer inline-flex items-center gap-1"
              title="Edit Destinasi & Foto"
            >
              <svg class="w-3.5 h-3.5 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
              </svg>
              <span>Edit</span>
            </button>

            <button
              type="button"
              @click="openDeleteModal(dest)"
              class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50/50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition cursor-pointer inline-flex items-center gap-1"
              title="Hapus Destinasi"
            >
              <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
              </svg>
              <span>Hapus</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="destinations.length === 0"
      class="bg-surface rounded-2xl border border-border p-12 text-center text-text-muted space-y-3"
    >
      <div class="text-4xl">🏔️</div>
      <h3 class="text-base font-bold text-text-main">Tidak ada destinasi di klaster ini</h3>
      <p class="text-xs max-w-sm mx-auto">
        Belum ada data destinasi untuk filter yang dipilih. Silakan tambah destinasi baru atau pilih klaster lain.
      </p>
      <button
        type="button"
        @click="showCreateModal = true"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold cursor-pointer"
      >
        <span>+ Tambah Destinasi</span>
      </button>
    </div>

    <!-- Modal 1: Create New Master Destination (Compact 88vh + Backdrop Dismiss) -->
    <div
      v-if="showCreateModal"
      @click.self="showCreateModal = false"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-2xl w-full max-h-[88vh] flex flex-col p-6 shadow-xl">
        <div class="flex items-start justify-between border-b border-border pb-3 shrink-0">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Master Catalog Engine</span>
            <h3 class="text-lg font-extrabold text-text-main">Tambah Destinasi Wisata Baru</h3>
          </div>
          <button type="button" @click="showCreateModal = false" class="text-text-muted hover:text-text-main font-bold p-1 cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitCreate" class="space-y-4 text-xs overflow-y-auto pr-1 mt-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block font-bold text-text-main mb-1">Nama Objek Wisata *</label>
              <input
                v-model="createForm.name"
                type="text"
                required
                placeholder="Misal: Bukit Holbung Samosir"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>

            <div>
              <label class="block font-bold text-text-main mb-1">Klaster Wilayah *</label>
              <select
                v-model="createForm.cluster"
                required
                class="w-full rounded-xl border-border text-xs font-semibold focus:border-primary focus:ring-primary"
              >
                <option v-for="c in clusters" :key="c.value" :value="c.value">{{ c.label }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block font-bold text-text-main mb-1">Kategori Objek</label>
              <select
                v-model="createForm.category_id"
                class="w-full rounded-xl border-border text-xs font-semibold focus:border-primary focus:ring-primary"
              >
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-text-main mb-1">Jam Operasional</label>
              <input
                v-model="createForm.operating_hours"
                type="text"
                placeholder="07:00 - 18:00 WIB"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-text-main mb-1">Deskripsi Lengkap & Daya Tarik Wisata *</label>
            <textarea
              v-model="createForm.description"
              rows="2"
              required
              placeholder="Jelaskan keindahan alam, panorama danau, spot foto favorit, serta tips berkunjung..."
              class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
              <label class="block font-bold text-text-main mb-1">Tiket Domestik (Rp)</label>
              <input
                v-model="createForm.ticket_price_domestic"
                type="number"
                min="0"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">Tiket Asing (Rp)</label>
              <input
                v-model="createForm.ticket_price_foreign"
                type="number"
                min="0"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">Latitude</label>
              <input
                v-model="createForm.latitude"
                type="number"
                step="any"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">Longitude</label>
              <input
                v-model="createForm.longitude"
                type="number"
                step="any"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
          </div>

          <!-- Multi-Photo URLs and File Upload -->
          <div class="p-4 rounded-xl bg-surface-muted border border-border space-y-3">
            <div class="flex items-center justify-between">
              <label class="font-bold text-text-main">Galeri Foto Destinasi (Bisa Lebih dari 1 Foto)</label>
              <button
                type="button"
                @click="addCreateImageUrlField"
                class="text-[11px] font-bold text-primary hover:underline cursor-pointer"
              >
                + Tambah URL Foto
              </button>
            </div>

            <div v-for="(url, idx) in createForm.image_urls" :key="idx" class="flex items-center gap-2">
              <input
                v-model="createForm.image_urls[idx]"
                type="url"
                placeholder="https://images.unsplash.com/photo-..."
                class="flex-1 rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
              <button
                v-if="createForm.image_urls.length > 1"
                type="button"
                @click="removeCreateImageUrlField(idx)"
                class="px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 font-bold cursor-pointer"
              >
                ✕
              </button>
            </div>

            <div class="pt-2 border-t border-border">
              <label class="block font-semibold text-text-main mb-1.5">
                Unggah Berkas Gambar (Pilih Banyak Sekaligus atau Bertahap — JPG, PNG, WebP)
              </label>
              <input
                type="file"
                multiple
                accept="image/jpeg,image/png,image/webp"
                @change="handleCreateFileChange"
                class="text-xs text-text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"
              />

              <!-- Preview Uploaded Files with Remove Button -->
              <div v-if="createPhotoPreviews.length > 0" class="flex flex-wrap gap-2.5 mt-3">
                <div
                  v-for="(item, i) in createPhotoPreviews"
                  :key="i"
                  class="relative w-16 h-16 rounded-xl overflow-hidden border border-border shadow-2xs group"
                >
                  <img :src="item.url" class="w-full h-full object-cover" />
                  <button
                    type="button"
                    @click="removeCreatePendingPhoto(i)"
                    class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center shadow-xs cursor-pointer"
                    title="Batal unggah foto ini"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-border">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-4 py-2 rounded-xl border border-border text-text-muted font-semibold hover:bg-surface-muted cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="createForm.processing"
              class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold shadow-xs cursor-pointer disabled:opacity-50"
            >
              {{ createForm.processing ? 'Menyimpan...' : 'Simpan Destinasi' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 2: Edit Master Destination (Compact 88vh + Backdrop Dismiss) -->
    <div
      v-if="showEditModal"
      @click.self="showEditModal = false"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-2xl w-full max-h-[88vh] flex flex-col p-6 shadow-xl">
        <div class="flex items-start justify-between border-b border-border pb-3 shrink-0">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Master Catalog Engine</span>
            <h3 class="text-lg font-extrabold text-text-main">Perbarui Destinasi: {{ editForm.name }}</h3>
          </div>
          <button type="button" @click="showEditModal = false" class="text-text-muted hover:text-text-main font-bold p-1 cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitUpdate" class="space-y-4 text-xs overflow-y-auto pr-1 mt-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block font-bold text-text-main mb-1">Nama Objek Wisata *</label>
              <input
                v-model="editForm.name"
                type="text"
                required
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>

            <div>
              <label class="block font-bold text-text-main mb-1">Klaster Wilayah *</label>
              <select
                v-model="editForm.cluster"
                required
                class="w-full rounded-xl border-border text-xs font-semibold focus:border-primary focus:ring-primary"
              >
                <option v-for="c in clusters" :key="c.value" :value="c.value">{{ c.label }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block font-bold text-text-main mb-1">Kategori Objek</label>
              <select
                v-model="editForm.category_id"
                class="w-full rounded-xl border-border text-xs font-semibold focus:border-primary focus:ring-primary"
              >
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-text-main mb-1">Jam Operasional</label>
              <input
                v-model="editForm.operating_hours"
                type="text"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-text-main mb-1">Deskripsi Lengkap & Daya Tarik Wisata *</label>
            <textarea
              v-model="editForm.description"
              rows="2"
              required
              class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
              <label class="block font-bold text-text-main mb-1">Tiket Domestik (Rp)</label>
              <input
                v-model="editForm.ticket_price_domestic"
                type="number"
                min="0"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">Tiket Asing (Rp)</label>
              <input
                v-model="editForm.ticket_price_foreign"
                type="number"
                min="0"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">Latitude</label>
              <input
                v-model="editForm.latitude"
                type="number"
                step="any"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
            <div>
              <label class="block font-bold text-text-main mb-1">Longitude</label>
              <input
                v-model="editForm.longitude"
                type="number"
                step="any"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
          </div>

          <!-- Existing Media Gallery Management -->
          <div class="p-4 rounded-xl bg-surface-muted border border-border space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <label class="font-bold text-text-main">Kelola Galeri Media Saat Ini</label>
                <p class="text-[11px] text-text-muted">Pilih foto utama atau tandai foto yang ingin dihapus dari server.</p>
              </div>
              <span class="text-[11px] font-bold text-text-muted">{{ editExistingMedia.length }} Foto Tersimpan</span>
            </div>

            <div v-if="editExistingMedia.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              <div
                v-for="media in editExistingMedia"
                :key="media.id"
                :class="editForm.deleted_media_ids.includes(media.id) ? 'opacity-40 grayscale border-rose-300 bg-rose-50' : 'border-border bg-surface'"
                class="relative rounded-xl border p-1.5 flex flex-col justify-between transition"
              >
                <div class="h-18 rounded-lg overflow-hidden bg-surface-muted mb-1.5">
                  <img :src="media.url" :alt="media.caption" class="w-full h-full object-cover" />
                </div>

                <div class="flex items-center justify-between text-[10px]">
                  <label class="flex items-center gap-1 cursor-pointer font-bold text-text-main">
                    <input
                      type="radio"
                      name="primary_media"
                      :value="media.id"
                      v-model="editForm.primary_media_id"
                      :disabled="editForm.deleted_media_ids.includes(media.id)"
                      class="text-primary focus:ring-primary"
                    />
                    <span>Utama</span>
                  </label>

                  <button
                    type="button"
                    @click="toggleDeleteMedia(media.id)"
                    class="font-bold cursor-pointer"
                    :class="editForm.deleted_media_ids.includes(media.id) ? 'text-primary' : 'text-rose-600 hover:underline'"
                  >
                    {{ editForm.deleted_media_ids.includes(media.id) ? 'Batal' : 'Hapus' }}
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="text-xs text-text-muted italic">Belum ada foto media tersimpan.</div>

            <!-- Upload More Photos or Add URLs -->
            <div class="pt-3 border-t border-border space-y-2.5">
              <div class="flex items-center justify-between">
                <span class="font-bold text-text-main">Tambah Foto Tambahan (Bisa Lebih dari 1 Foto)</span>
                <button
                  type="button"
                  @click="addEditImageUrlField"
                  class="text-[11px] font-bold text-primary hover:underline cursor-pointer"
                >
                  + Tambah Baris URL Foto
                </button>
              </div>

              <div v-for="(url, idx) in editForm.image_urls" :key="idx" class="flex items-center gap-2">
                <input
                  v-model="editForm.image_urls[idx]"
                  type="url"
                  placeholder="https://images.unsplash.com/photo-..."
                  class="flex-1 rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
                />
                <button
                  v-if="editForm.image_urls.length > 1"
                  type="button"
                  @click="removeEditImageUrlField(idx)"
                  class="px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 font-bold cursor-pointer"
                >
                  ✕
                </button>
              </div>

              <div>
                <label class="block font-semibold text-text-main mb-1">
                  Unggah File Foto Baru (Pilih Banyak Sekaligus atau Bertahap — JPG, PNG, WebP)
                </label>
                <input
                  type="file"
                  multiple
                  accept="image/jpeg,image/png,image/webp"
                  @change="handleEditFileChange"
                  class="text-xs text-text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"
                />

                <div v-if="editPhotoPreviews.length > 0" class="flex flex-wrap gap-2.5 mt-2.5">
                  <div
                    v-for="(item, i) in editPhotoPreviews"
                    :key="i"
                    class="relative w-16 h-16 rounded-xl overflow-hidden border border-border shadow-2xs"
                  >
                    <img :src="item.url" class="w-full h-full object-cover" />
                    <button
                      type="button"
                      @click="removeEditPendingPhoto(i)"
                      class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center shadow-xs cursor-pointer"
                      title="Batal unggah foto ini"
                    >
                      ✕
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between pt-3 border-t border-border">
            <label class="flex items-center gap-2 font-bold text-text-main cursor-pointer">
              <input
                type="checkbox"
                v-model="editForm.is_active"
                class="rounded border-border text-primary focus:ring-primary"
              />
              <span>Destinasi Aktif di Katalog</span>
            </label>

            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="showEditModal = false"
                class="px-4 py-2 rounded-xl border border-border text-text-muted font-semibold hover:bg-surface-muted cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="editForm.processing"
                class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold shadow-xs cursor-pointer disabled:opacity-50"
              >
                {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 3: Delete Confirmation Modal (with Backdrop Dismiss) -->
    <div
      v-if="showDeleteModal"
      @click.self="showDeleteModal = false"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg shrink-0">
            ⚠️
          </div>
          <div>
            <h3 class="text-base font-extrabold text-text-main">Konfirmasi Hapus Destinasi</h3>
            <p class="text-xs text-text-muted">{{ selectedDestForDelete?.name }}</p>
          </div>
        </div>

        <div
          v-if="selectedDestForDelete?.stops_usage_count > 0"
          class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-2"
        >
          <div class="font-bold flex items-center gap-1.5">
            <span>🛡️ Destinasi Terikat di {{ selectedDestForDelete.stops_usage_count }} Rute Tur</span>
          </div>
          <p class="leading-relaxed">
            Destinasi ini sedang digunakan dalam paket tur aktif. Untuk menjaga konsistensi data riwayat perjalanan dan snapshot pemesanan, penghapusan permanen dinonaktifkan.
          </p>
          <p class="font-bold">
            Rekomendasi: Nonaktifkan (toggle status) destinasi agar tidak muncul lagi pada rute baru.
          </p>
        </div>

        <p v-else class="text-xs text-text-muted leading-relaxed">
          Apakah Anda yakin ingin menghapus destinasi <span class="font-bold text-text-main">{{ selectedDestForDelete?.name }}</span> beserta seluruh berkas foto galerinya dari disk server secara permanen?
        </p>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-border">
          <button
            type="button"
            @click="showDeleteModal = false"
            class="px-4 py-2 rounded-xl border border-border text-text-muted font-semibold text-xs hover:bg-surface-muted cursor-pointer"
          >
            Batal
          </button>

          <button
            v-if="selectedDestForDelete?.stops_usage_count > 0"
            type="button"
            @click="toggleStatus(selectedDestForDelete); showDeleteModal = false;"
            class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs cursor-pointer"
          >
            Nonaktifkan Saja
          </button>

          <button
            v-else
            type="button"
            @click="confirmDelete"
            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs cursor-pointer"
          >
            Ya, Hapus Permanen
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
