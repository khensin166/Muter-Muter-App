<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  users: Object,
  counts: Object,
  partners: Array,
  filters: Object,
  currentAdminId: String,
});

const searchInput = ref(props.filters.search || '');
const isRoleModalOpen = ref(false);
const selectedUser = ref(null);

const roleForm = useForm({
  role: 'CUSTOMER',
  partner_id: '',
  partner_role: 'OWNER',
});

const applyFilters = (newRole = null) => {
  router.get(
    route('admin.users.index'),
    {
      role: newRole !== null ? newRole : props.filters.role,
      search: searchInput.value || undefined,
    },
    { preserveState: true, replace: true }
  );
};

const handleSearch = () => {
  applyFilters();
};

const resetFilters = () => {
  searchInput.value = '';
  router.get(route('admin.users.index'), {}, { replace: true });
};

const openRoleModal = (user) => {
  selectedUser.value = user;
  roleForm.role = user.role;
  roleForm.partner_id = user.partner?.id || '';
  roleForm.partner_role = user.partner?.role || 'OWNER';
  isRoleModalOpen.value = true;
};

const submitRoleUpdate = () => {
  roleForm.patch(route('admin.users.update-role', selectedUser.value.id), {
    onSuccess: () => {
      isRoleModalOpen.value = false;
      selectedUser.value = null;
      roleForm.reset();
    },
  });
};

const roleTabs = [
  { key: 'ALL', label: 'Semua Pengguna', count: props.counts.all },
  { key: 'CUSTOMER', label: 'Pelanggan / Wisatawan', count: props.counts.customer, color: 'text-blue-700 bg-blue-50' },
  { key: 'PARTNER', label: 'Mitra Rental / Vendor', count: props.counts.partner, color: 'text-emerald-700 bg-emerald-50' },
  { key: 'ADMIN', label: 'Pengelola (Admin)', count: props.counts.admin, color: 'text-purple-700 bg-purple-50' },
];
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Pengguna & RBAC — Admin Console" />

    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-surface rounded-2xl p-6 border border-border shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-black uppercase tracking-wider text-purple-700 bg-purple-50 border border-purple-200/80 px-2.5 py-0.5 rounded-full">
              Keamanan & Kontrol Akses (RBAC)
            </span>
          </div>
          <h1 class="text-2xl font-black text-text-main mt-1.5">Manajemen Pengguna & Hak Akses</h1>
          <p class="text-xs text-text-muted mt-0.5">
            Kelola peran pengguna marketplace: Wisatawan (Customer), Mitra Vendor (Partner), Administrator, dan Super Admin.
          </p>
        </div>

        <!-- Quick Summary Badges -->
        <div class="flex flex-wrap items-center gap-2">
          <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs">
            <span class="text-slate-400 block text-[10px] uppercase font-bold">Total User</span>
            <span class="font-black text-slate-800 text-sm">{{ counts.all }} Akun</span>
          </div>
          <div class="px-3.5 py-2 rounded-xl bg-blue-50 border border-blue-200 text-xs">
            <span class="text-blue-600 block text-[10px] uppercase font-bold">Pelanggan</span>
            <span class="font-black text-blue-800 text-sm">{{ counts.customer }}</span>
          </div>
          <div class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs">
            <span class="text-emerald-600 block text-[10px] uppercase font-bold">Mitra Rental</span>
            <span class="font-black text-emerald-800 text-sm">{{ counts.partner }}</span>
          </div>
          <div class="px-3.5 py-2 rounded-xl bg-purple-50 border border-purple-200 text-xs">
            <span class="text-purple-600 block text-[10px] uppercase font-bold">Pengelola</span>
            <span class="font-black text-purple-800 text-sm">{{ counts.admin }}</span>
          </div>
        </div>
      </div>

      <!-- Controls: Role Tabs & Search Bar -->
      <div class="bg-surface rounded-2xl p-4 border border-border shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Role Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
          <button
            v-for="tab in roleTabs"
            :key="tab.key"
            type="button"
            @click="applyFilters(tab.key)"
            :class="[
              'px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer tactile-press',
              filters.role === tab.key
                ? 'bg-primary text-white shadow-xs'
                : 'bg-surface border border-border text-text-muted hover:text-text-main hover:bg-surface-muted'
            ]"
          >
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.count !== undefined"
              :class="[
                'px-2 py-0.5 rounded-full text-[10px] font-black',
                filters.role === tab.key ? 'bg-white/20 text-white' : (tab.color || 'bg-slate-100 text-slate-700')
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>

        <!-- Search Input Form -->
        <form @submit.prevent="handleSearch" class="flex items-center gap-2">
          <div class="relative w-full sm:w-80">
            <input
              v-model="searchInput"
              type="text"
              placeholder="Cari nama, email, no telepon..."
              class="w-full text-xs pl-9 pr-8 py-2 rounded-xl border border-border bg-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
            />
            <svg class="w-4 h-4 text-text-muted absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <button
              v-if="searchInput"
              type="button"
              @click="resetFilters"
              class="absolute right-2.5 top-2.5 text-text-muted hover:text-text-main text-xs"
            >
              ✕
            </button>
          </div>
          <button
            type="submit"
            class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition shrink-0 tactile-press cursor-pointer"
          >
            Cari
          </button>
        </form>
      </div>

      <!-- Users Table Section -->
      <div class="bg-surface rounded-2xl border border-border shadow-xs overflow-hidden">
        <div class="p-5 border-b border-border flex items-center justify-between">
          <h2 class="text-sm font-bold text-text-main">
            Daftar Akun Pengguna ({{ users.total }} ditemukan)
          </h2>
          <span class="text-xs text-text-muted">Halaman {{ users.current_page }} dari {{ users.last_page }}</span>
        </div>

        <div v-if="users.data.length === 0" class="p-16 text-center">
          <div class="w-16 h-16 rounded-2xl bg-surface-muted text-text-muted flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
          </div>
          <h3 class="text-sm font-bold text-text-main">Tidak ada data pengguna</h3>
          <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">
            Tidak ditemukan akun pengguna yang cocok dengan kriteria filter atau kata kunci pencarian Anda.
          </p>
          <button
            v-if="filters.role !== 'ALL' || filters.search"
            @click="resetFilters"
            class="mt-4 px-4 py-2 bg-primary/10 text-primary text-xs font-bold rounded-xl hover:bg-primary/20 transition cursor-pointer"
          >
            Reset Semua Filter
          </button>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-border bg-surface-muted text-[10px] font-black uppercase tracking-wider text-text-muted">
                <th class="py-3 px-4">Nama & Email</th>
                <th class="py-3 px-4">Nomor Telepon</th>
                <th class="py-3 px-4">Hak Akses (Role)</th>
                <th class="py-3 px-4">Afiliasi Mitra</th>
                <th class="py-3 px-4">Total Booking</th>
                <th class="py-3 px-4">Terdaftar</th>
                <th class="py-3 px-4 text-right">Aksi RBAC</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr
                v-for="u in users.data"
                :key="u.id"
                class="hover:bg-surface-muted/60 transition group"
              >
                <!-- Name & Email with Avatar -->
                <td class="py-4 px-4 align-middle">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary font-black text-xs flex items-center justify-center uppercase shrink-0">
                      {{ u.name.slice(0, 2) }}
                    </div>
                    <div>
                      <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span>{{ u.name }}</span>
                        <span
                          v-if="u.id === currentAdminId"
                          class="px-1.5 py-0.2 bg-primary/10 text-primary text-[10px] font-bold rounded"
                        >
                          Akun Anda
                        </span>
                      </div>
                      <div class="text-[11px] text-text-muted mt-0.5">{{ u.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Phone -->
                <td class="py-4 px-4 align-middle text-slate-700 font-medium">
                  {{ u.phone || '-' }}
                </td>

                <!-- Role Badge -->
                <td class="py-4 px-4 align-middle">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider inline-block',
                      u.role === 'SUPER_ADMIN' ? 'bg-purple-100 text-purple-900 border border-purple-300 font-black' :
                      u.role === 'ADMIN' ? 'bg-purple-50 text-purple-800 border border-purple-200' :
                      u.role === 'PARTNER' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' :
                      'bg-blue-50 text-blue-800 border border-blue-200'
                    ]"
                  >
                    {{ u.role }}
                  </span>
                </td>

                <!-- Partner Affiliation -->
                <td class="py-4 px-4 align-middle">
                  <div v-if="u.partner" class="space-y-0.5">
                    <div class="font-bold text-text-main flex items-center gap-1">
                      <span>🏢</span>
                      <span>{{ u.partner.name }}</span>
                    </div>
                    <div class="text-[10px] text-text-muted">
                      📍 {{ u.partner.city }} • <span class="font-semibold text-emerald-700">{{ u.partner.role }}</span>
                    </div>
                  </div>
                  <span v-else class="text-text-muted text-[11px] italic">Bukan Mitra</span>
                </td>

                <!-- Bookings Count -->
                <td class="py-4 px-4 align-middle font-bold text-slate-800">
                  <span v-if="u.bookings_count > 0" class="text-primary font-black">
                    {{ u.bookings_count }} Pesanan
                  </span>
                  <span v-else class="text-text-muted font-normal text-[11px]">
                    0
                  </span>
                </td>

                <!-- Registration Date -->
                <td class="py-4 px-4 align-middle text-text-muted text-[11px]">
                  {{ u.created_at }}
                </td>

                <!-- Actions -->
                <td class="py-4 px-4 align-middle text-right">
                  <button
                    v-if="u.id !== currentAdminId"
                    type="button"
                    @click="openRoleModal(u)"
                    class="px-3 py-1.5 rounded-lg bg-surface border border-border text-slate-700 hover:text-primary hover:border-primary/50 text-xs font-bold transition tactile-press cursor-pointer"
                  >
                    Ubah Role
                  </button>
                  <span
                    v-else
                    class="text-[11px] text-text-muted italic px-2 py-1 bg-slate-100 rounded-lg"
                  >
                    Terkunci
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.links && users.links.length > 3" class="p-4 border-t border-border flex items-center justify-center gap-1">
          <Link
            v-for="(link, i) in users.links"
            :key="i"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-semibold transition',
              link.active ? 'bg-primary text-white font-bold' : 'text-text-muted hover:text-text-main hover:bg-surface-muted',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
          />
        </div>
      </div>
    </div>

    <!-- Modal Ubah Hak Akses (Role Switcher) -->
    <div
      v-if="isRoleModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-2xs"
    >
      <div class="bg-surface rounded-2xl border border-border shadow-2xl max-w-lg w-full p-6 animate-scale-up">
        <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
          <div class="flex items-center gap-2">
            <span class="text-lg">🛡️</span>
            <div>
              <h3 class="text-sm font-black text-text-main">Ubah Hak Akses & Peran Pengguna</h3>
              <p class="text-[11px] text-text-muted">{{ selectedUser?.name }} ({{ selectedUser?.email }})</p>
            </div>
          </div>
          <button
            @click="isRoleModalOpen = false"
            class="text-text-muted hover:text-text-main text-sm font-bold p-1 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="submitRoleUpdate" class="space-y-4">
          <!-- Pilih Role Utama -->
          <div>
            <label class="block text-xs font-bold text-text-main mb-1.5">
              Pilih Peran Baru (Role) <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-2">
              <label
                :class="[
                  'p-3 rounded-xl border text-xs font-bold cursor-pointer transition flex flex-col gap-1',
                  roleForm.role === 'CUSTOMER' ? 'border-blue-500 bg-blue-50/70 text-blue-900 ring-1 ring-blue-500' : 'border-border hover:bg-surface-muted text-slate-700'
                ]"
              >
                <div class="flex items-center justify-between">
                  <span>Pelanggan</span>
                  <input type="radio" value="CUSTOMER" v-model="roleForm.role" class="text-primary" />
                </div>
                <span class="text-[10px] text-slate-500 font-normal">Dapat memesan tur & rental mobil</span>
              </label>

              <label
                :class="[
                  'p-3 rounded-xl border text-xs font-bold cursor-pointer transition flex flex-col gap-1',
                  roleForm.role === 'PARTNER' ? 'border-emerald-500 bg-emerald-50/70 text-emerald-900 ring-1 ring-emerald-500' : 'border-border hover:bg-surface-muted text-slate-700'
                ]"
              >
                <div class="flex items-center justify-between">
                  <span>Mitra Vendor</span>
                  <input type="radio" value="PARTNER" v-model="roleForm.role" class="text-emerald-600" />
                </div>
                <span class="text-[10px] text-slate-500 font-normal">Akses portal mitra & kelola armada</span>
              </label>

              <label
                :class="[
                  'p-3 rounded-xl border text-xs font-bold cursor-pointer transition flex flex-col gap-1',
                  roleForm.role === 'ADMIN' ? 'border-purple-500 bg-purple-50/70 text-purple-900 ring-1 ring-purple-500' : 'border-border hover:bg-surface-muted text-slate-700'
                ]"
              >
                <div class="flex items-center justify-between">
                  <span>Admin Operasional</span>
                  <input type="radio" value="ADMIN" v-model="roleForm.role" class="text-purple-600" />
                </div>
                <span class="text-[10px] text-slate-500 font-normal">Akses penuh panel operasional</span>
              </label>

              <label
                :class="[
                  'p-3 rounded-xl border text-xs font-bold cursor-pointer transition flex flex-col gap-1',
                  roleForm.role === 'SUPER_ADMIN' ? 'border-slate-800 bg-slate-900 text-white ring-1 ring-slate-800' : 'border-border hover:bg-surface-muted text-slate-700'
                ]"
              >
                <div class="flex items-center justify-between">
                  <span>Super Admin</span>
                  <input type="radio" value="SUPER_ADMIN" v-model="roleForm.role" class="text-slate-900" />
                </div>
                <span class="text-[10px] text-slate-400 font-normal">Hak akses absolut sistem</span>
              </label>
            </div>
          </div>

          <!-- Pengaturan Khusus Mitra jika memilih PARTNER -->
          <div v-if="roleForm.role === 'PARTNER'" class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/80 space-y-3 animate-fade-in">
            <div>
              <label class="block text-xs font-bold text-emerald-950 mb-1">
                Tautkan ke Perusahaan Mitra (Opsional)
              </label>
              <select
                v-model="roleForm.partner_id"
                class="w-full text-xs p-2.5 rounded-xl border border-emerald-300 bg-white focus:outline-none focus:border-emerald-600"
              >
                <option value="">-- Belum Ditautkan (Mitra Lepas) --</option>
                <option
                  v-for="p in partners"
                  :key="p.id"
                  :value="p.id"
                >
                  {{ p.business_name }} ({{ p.city }})
                </option>
              </select>
            </div>

            <div v-if="roleForm.partner_id">
              <label class="block text-xs font-bold text-emerald-950 mb-1">
                Jabatan di Perusahaan Mitra
              </label>
              <div class="flex items-center gap-4 text-xs font-semibold text-emerald-900">
                <label class="flex items-center gap-1.5 cursor-pointer">
                  <input type="radio" value="OWNER" v-model="roleForm.partner_role" />
                  <span>Owner (Pemilik Penuh)</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                  <input type="radio" value="STAFF" v-model="roleForm.partner_role" />
                  <span>Staf / Supir Operasional</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Peringatan Aturan Bisnis -->
          <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2">
            <span>ℹ️</span>
            <div class="leading-relaxed text-[11px]">
              <strong>Perhatian:</strong> Akun dengan peran <strong>Admin</strong> atau <strong>Mitra</strong> secara otomatis dilarang melakukan pemesanan wisata/rental untuk mencegah konflik kepentingan sistem.
            </div>
          </div>

          <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
            <button
              type="button"
              @click="isRoleModalOpen = false"
              class="px-4 py-2 text-xs font-bold text-text-muted hover:bg-surface-muted rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="roleForm.processing"
              class="px-5 py-2 text-xs font-bold bg-primary text-white rounded-xl hover:bg-primary-dark transition disabled:opacity-50 tactile-press cursor-pointer"
            >
              {{ roleForm.processing ? 'Menyimpan...' : 'Simpan Hak Akses' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

