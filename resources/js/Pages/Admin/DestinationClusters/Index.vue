<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  clusters: {
    type: Array,
    default: () => [],
  },
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedClusterForDelete = ref(null);

const createForm = useForm({
  code: '',
  name: '',
  description: '',
  province: 'Sumatera Utara',
  sort_order: (props.clusters?.length || 0) + 1,
  is_active: true,
});

const editForm = useForm({
  id: '',
  code: '',
  name: '',
  description: '',
  province: 'Sumatera Utara',
  sort_order: 1,
  is_active: true,
});

const submitCreate = () => {
  createForm.post(route('admin.destination-clusters.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const openEditModal = (cluster) => {
  editForm.id = cluster.id;
  editForm.code = cluster.code;
  editForm.name = cluster.name;
  editForm.description = cluster.description || '';
  editForm.province = cluster.province || 'Sumatera Utara';
  editForm.sort_order = cluster.sort_order || 1;
  editForm.is_active = cluster.is_active ?? true;
  showEditModal.value = true;
};

const submitUpdate = () => {
  editForm.post(route('admin.destination-clusters.update', editForm.id), {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
};

const openDeleteModal = (cluster) => {
  selectedClusterForDelete.value = cluster;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!selectedClusterForDelete.value) return;
  router.delete(route('admin.destination-clusters.destroy', selectedClusterForDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedClusterForDelete.value = null;
    },
  });
};

const toggleStatus = (cluster) => {
  router.patch(route('admin.destination-clusters.toggle', cluster.id), {}, {
    preserveScroll: true,
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Klaster Wilayah — Admin Console" />

    <!-- Header -->
    <div class="bg-surface rounded-2xl p-6 border border-border mb-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 text-[11px] font-bold text-primary uppercase tracking-wider mb-1">
          <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
          <span>Master Data Geografis & Logistik</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-text-main tracking-tight">
          Manajemen Klaster Wilayah Wisata
        </h1>
        <p class="text-xs text-text-muted mt-1">
          Kelola zona geografis untuk pengelompokan destinasi, perhitungan jarak rute, dan penyaringan katalog publik.
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <Link
          href="/admin/destinations"
          class="px-3.5 py-2.5 rounded-xl border border-border bg-surface hover:bg-surface-muted text-text-main text-xs font-bold transition inline-flex items-center gap-1.5"
        >
          <span>← Kembali ke Destinasi</span>
        </Link>
        <button
          type="button"
          @click="showCreateModal = true"
          class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs font-bold shadow-xs transition cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>Tambah Klaster Baru</span>
        </button>
      </div>
    </div>

    <!-- Clusters Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="cluster in clusters"
        :key="cluster.id"
        class="bg-surface rounded-2xl border border-border p-5 shadow-2xs hover:shadow-md transition flex flex-col justify-between gap-4"
      >
        <div>
          <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-1 rounded-lg bg-primary/10 text-primary font-mono text-[11px] font-black">
                {{ cluster.code }}
              </span>
              <span class="text-[11px] font-semibold text-text-muted">
                Urutan #{{ cluster.sort_order }}
              </span>
            </div>

            <button
              type="button"
              @click="toggleStatus(cluster)"
              :class="cluster.is_active ? 'bg-emerald-600 text-white' : 'bg-stone-200 text-stone-700'"
              class="px-2.5 py-1 rounded-lg text-[10px] font-bold cursor-pointer transition inline-flex items-center gap-1"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="cluster.is_active ? 'bg-white' : 'bg-stone-500'"></span>
              <span>{{ cluster.is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </button>
          </div>

          <h3 class="text-base font-extrabold text-text-main leading-snug">
            {{ cluster.name }}
          </h3>

          <p class="text-xs text-text-muted mt-1.5 line-clamp-3 leading-relaxed">
            {{ cluster.description || 'Zona pariwisata regional ' + cluster.province }}
          </p>
        </div>

        <div class="pt-3.5 border-t border-border flex items-center justify-between text-xs">
          <Link
            :href="`/admin/destinations?cluster=${cluster.code}`"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-muted border border-border text-text-main hover:border-primary/40 hover:text-primary font-bold text-[11px] transition"
          >
            <span>📍 {{ cluster.destinations_count }} Destinasi</span>
          </Link>

          <div class="flex items-center gap-1.5">
            <button
              type="button"
              @click="openEditModal(cluster)"
              class="px-2.5 py-1.5 rounded-lg border border-border bg-surface hover:bg-surface-muted text-text-main text-xs font-bold transition cursor-pointer inline-flex items-center gap-1"
            >
              <svg class="w-3.5 h-3.5 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
              </svg>
              <span>Edit</span>
            </button>

            <button
              type="button"
              @click="openDeleteModal(cluster)"
              class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50/50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition cursor-pointer inline-flex items-center gap-1"
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

    <!-- Modal 1: Create Cluster -->
    <div
      v-if="showCreateModal"
      @click.self="showCreateModal = false"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-lg w-full max-h-[88vh] flex flex-col p-6 shadow-xl">
        <div class="flex items-start justify-between border-b border-border pb-3 shrink-0">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Master Zona Geografis</span>
            <h3 class="text-lg font-extrabold text-text-main">Tambah Klaster Wilayah Baru</h3>
          </div>
          <button type="button" @click="showCreateModal = false" class="text-text-muted hover:text-text-main font-bold p-1 cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitCreate" class="space-y-4 text-xs overflow-y-auto pr-1 mt-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
              <label class="block font-bold text-text-main mb-1">Kode Klaster (Unik) *</label>
              <input
                v-model="createForm.code"
                type="text"
                required
                placeholder="Misal: BUKIT_LAWANG atau NIAS_SELATAN"
                class="w-full rounded-xl border-border text-xs font-mono uppercase focus:border-primary focus:ring-primary"
              />
              <span v-if="createForm.errors.code" class="text-rose-600 text-[11px] mt-1 block">{{ createForm.errors.code }}</span>
            </div>

            <div>
              <label class="block font-bold text-text-main mb-1">Urutan Tampil</label>
              <input
                v-model="createForm.sort_order"
                type="number"
                min="0"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-text-main mb-1">Nama Klaster Wilayah *</label>
            <input
              v-model="createForm.name"
              type="text"
              required
              placeholder="Misal: Bukit Lawang & Ekowisata Gunung Leuser"
              class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
            />
          </div>

          <div>
            <label class="block font-bold text-text-main mb-1">Deskripsi Cakupan Wilayah & Logistik</label>
            <textarea
              v-model="createForm.description"
              rows="3"
              placeholder="Jelaskan cakupan kecamatan/kabupaten, akses jalan, atau karakteristik wisata..."
              class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
            ></textarea>
          </div>

          <div class="flex items-center justify-between pt-3 border-t border-border">
            <label class="flex items-center gap-2 font-bold text-text-main cursor-pointer">
              <input
                type="checkbox"
                v-model="createForm.is_active"
                class="rounded border-border text-primary focus:ring-primary"
              />
              <span>Aktifkan di Katalog & Form</span>
            </label>

            <div class="flex items-center gap-2">
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
                {{ createForm.processing ? 'Menyimpan...' : 'Simpan Klaster' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 2: Edit Cluster -->
    <div
      v-if="showEditModal"
      @click.self="showEditModal = false"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
    >
      <div class="bg-surface rounded-2xl border border-border max-w-lg w-full max-h-[88vh] flex flex-col p-6 shadow-xl">
        <div class="flex items-start justify-between border-b border-border pb-3 shrink-0">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-primary">Master Zona Geografis</span>
            <h3 class="text-lg font-extrabold text-text-main">Edit Klaster: {{ editForm.code }}</h3>
          </div>
          <button type="button" @click="showEditModal = false" class="text-text-muted hover:text-text-main font-bold p-1 cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitUpdate" class="space-y-4 text-xs overflow-y-auto pr-1 mt-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
              <label class="block font-bold text-text-main mb-1">Kode Klaster (Unik) *</label>
              <input
                v-model="editForm.code"
                type="text"
                required
                class="w-full rounded-xl border-border text-xs font-mono uppercase focus:border-primary focus:ring-primary"
              />
              <span v-if="editForm.errors.code" class="text-rose-600 text-[11px] mt-1 block">{{ editForm.errors.code }}</span>
            </div>

            <div>
              <label class="block font-bold text-text-main mb-1">Urutan Tampil</label>
              <input
                v-model="editForm.sort_order"
                type="number"
                min="0"
                class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-text-main mb-1">Nama Klaster Wilayah *</label>
            <input
              v-model="editForm.name"
              type="text"
              required
              class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
            />
          </div>

          <div>
            <label class="block font-bold text-text-main mb-1">Deskripsi Cakupan Wilayah & Logistik</label>
            <textarea
              v-model="editForm.description"
              rows="3"
              class="w-full rounded-xl border-border text-xs focus:border-primary focus:ring-primary"
            ></textarea>
          </div>

          <div class="flex items-center justify-between pt-3 border-t border-border">
            <label class="flex items-center gap-2 font-bold text-text-main cursor-pointer">
              <input
                type="checkbox"
                v-model="editForm.is_active"
                class="rounded border-border text-primary focus:ring-primary"
              />
              <span>Aktifkan di Katalog & Form</span>
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

    <!-- Modal 3: Delete Cluster Confirmation -->
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
            <h3 class="text-base font-extrabold text-text-main">Hapus Klaster Wilayah</h3>
            <p class="text-xs text-text-muted">{{ selectedClusterForDelete?.name }} ({{ selectedClusterForDelete?.code }})</p>
          </div>
        </div>

        <div
          v-if="selectedClusterForDelete?.destinations_count > 0"
          class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-2"
        >
          <div class="font-bold">🛡️ Klaster Terhubung ke {{ selectedClusterForDelete.destinations_count }} Destinasi</div>
          <p class="leading-relaxed">
            Klaster ini tidak dapat dihapus permanen karena sedang digunakan oleh destinasi aktif. Silakan nonaktifkan status klaster atau pindahkan destinasi terlebih dahulu.
          </p>
        </div>

        <p v-else class="text-xs text-text-muted leading-relaxed">
          Apakah Anda yakin ingin menghapus klaster wilayah <span class="font-bold text-text-main">{{ selectedClusterForDelete?.name }}</span> secara permanen?
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
            v-if="selectedClusterForDelete?.destinations_count > 0"
            type="button"
            @click="toggleStatus(selectedClusterForDelete); showDeleteModal = false;"
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

