<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  destinations: {
    type: Array,
    default: () => [],
  },
  tourTypes: {
    type: Array,
    default: () => [],
  },
  partners: {
    type: Array,
    default: () => [],
  },
});

const activeLang = ref('id');

const presetInclusionOptions = [
  'Armada Mobil Bersih, Harum & Full AC Dingin',
  'BBM Pulang-Pergi (Fuel Included)',
  'Driver Profesional merangkap Guide Lokal',
  'Makan & Uang Rokok Supir (Bebas Pungli / Tips Canggung)',
  'Tiket Kapal Ferry Danau Toba PP Terjadwal',
  'Dokumentasi Foto, Video & Drone',
  'Air Mineral & Snack Perjalanan',
  'Garansi Armada Pengganti 1-2 Jam di Rute Kaldera',
];

const selectedPresets = ref([
  'Armada Mobil Bersih, Harum & Full AC Dingin',
  'BBM Pulang-Pergi (Fuel Included)',
  'Driver Profesional merangkap Guide Lokal',
  'Makan & Uang Rokok Supir (Bebas Pungli / Tips Canggung)',
  'Tiket Kapal Ferry Danau Toba PP Terjadwal',
  'Garansi Armada Pengganti 1-2 Jam di Rute Kaldera',
]);

const form = useForm({
  name: '',
  name_en: '',
  short_description: '',
  short_description_en: '',
  full_description: '',
  full_description_en: '',
  tour_type: 'PRIVATE_TRIP',
  base_price: 1300000,
  pricing_type: 'PER_GROUP',
  dp_percentage: 30,
  partner_id: '',
  duration_days: 1,
  duration_nights: 0,
  min_participants: 1,
  max_participants: 6,
  includes_ferry: true,
  inclusions: '',
  exclusions: 'Tiket pesawat PP, pengeluaran pribadi, dan makan peserta di luar program.',
  terms_conditions: 'Pemesanan minimal H-1 (24 jam). DP 30% mengunci armada. Termasuk garansi unit pengganti 1-2 jam dan standar mobil bersih bebas asap rokok.',
  photos: [],
  itinerary: [
    {
      day_number: 1,
      title: 'Hari 1: Eksplorasi Kaldera Danau Toba & Pulau Samosir',
      description: 'Penjemputan pagi hari dengan armada bersih ber-AC dingin menuju spot panorama terbaik Danau Toba.',
      stops: [
        {
          destination_id: props.destinations[0]?.id || '',
          title: props.destinations[0]?.name || 'Kunjungan Destinasi Utama',
          activity_description: props.destinations[0]?.short_description || 'Menikmati panorama alam dan sesi dokumentasi foto.',
          estimated_minutes: 90,
        },
      ],
    },
  ],
});

const getDestinationById = (id) => {
  if (!id) return null;
  return props.destinations.find((d) => d.id === id) || null;
};

const onSelectDestination = (stop) => {
  const dest = getDestinationById(stop.destination_id);
  if (dest) {
    stop.title = dest.name;
    if (!stop.activity_description) {
      stop.activity_description = dest.short_description || `Eksplorasi dan dokumentasi foto di ${dest.name}.`;
    }
  }
};

// All bound destinations across the itinerary for live photo gallery preview
const boundDestinationsPreview = computed(() => {
  const map = new Map();
  form.itinerary.forEach((day) => {
    (day.stops || []).forEach((stop) => {
      const dest = getDestinationById(stop.destination_id);
      if (dest && !map.has(dest.id)) {
        map.set(dest.id, dest);
      }
    });
  });
  return Array.from(map.values());
});

const addDay = () => {
  const nextDay = form.itinerary.length + 1;
  form.itinerary.push({
    day_number: nextDay,
    title: `Hari ${nextDay}: Rute Perjalanan Lanjutan`,
    description: '',
    stops: [
      {
        destination_id: '',
        title: '',
        activity_description: '',
        estimated_minutes: 60,
      },
    ],
  });
  form.duration_days = form.itinerary.length;
  form.duration_nights = Math.max(0, form.itinerary.length - 1);
};

const removeDay = (dayIdx) => {
  if (form.itinerary.length <= 1) return;
  form.itinerary.splice(dayIdx, 1);
  form.itinerary.forEach((d, i) => {
    d.day_number = i + 1;
  });
  form.duration_days = form.itinerary.length;
  form.duration_nights = Math.max(0, form.itinerary.length - 1);
};

const addStop = (dayIdx) => {
  form.itinerary[dayIdx].stops.push({
    destination_id: '',
    title: '',
    activity_description: '',
    estimated_minutes: 60,
  });
};

const removeStop = (dayIdx, stopIdx) => {
  if (form.itinerary[dayIdx].stops.length <= 1) return;
  form.itinerary[dayIdx].stops.splice(stopIdx, 1);
};

const loadFamtrip6D5NTemplate = () => {
  const d0 = props.destinations[0]?.id || '';
  const d1 = props.destinations[1]?.id || d0;
  const d2 = props.destinations[2]?.id || d0;
  const d3 = props.destinations[3]?.id || d0;

  form.name = '6D5N Famtrip North Sumatra: KNO - Bukit Lawang - Medan - Danau Toba & Samosir';
  form.short_description = 'Paket lengkap 6 Hari 5 Malam lintas koridor Sumatera Utara: Orangutan Bukit Lawang, Heritage Medan, Kapal Kayu Parapat, dan Eksplorasi Pulau Samosir (Termasuk BBM, Makan & Rokok Supir).';
  form.full_description = 'Program perjalanan resmi terkurasi selama 6 Hari 5 Malam menjelajahi pesona utama Sumatera Utara. Mulai dari penjemputan Bandara Kualanamu (KNO), petualangan hutan tropis Bukit Lawang Taman Nasional Gunung Leuser, wisata sejarah Kota Medan, penyeberangan kapal menuju Pulau Samosir, hingga kembali ke Medan via Jalan Tol dengan jaminan armada bersih dan layanan pengemudi profesional.';
  form.tour_type = 'PRIVATE_TRIP';
  form.pricing_type = 'PER_GROUP';
  form.base_price = 7300000;
  form.duration_days = 6;
  form.duration_nights = 5;
  form.min_participants = 2;
  form.max_participants = 6;
  form.includes_ferry = true;

  form.itinerary = [
    {
      day_number: 1,
      title: 'Hari 1 (11 Okt): Arrival Bandara KNO - Perjalanan ke Bukit Lawang (Rp 1.200.000)',
      description: 'Penjemputan di Bandara KNO (Welcome Dance), perjalanan menuju Bukit Lawang, check-in Eco Lodge Resort & Welcome Dinner.',
      stops: [
        {
          destination_id: d0,
          title: 'Bandara Kualanamu (KNO) menuju Bukit Lawang Eco Lodge',
          activity_description: 'Penjemputan di gerbang kedatangan KNO, perjalanan darat menuju kawasan konservasi Bukit Lawang, check-in dan makan malam.',
          estimated_minutes: 210,
        },
      ],
    },
    {
      day_number: 2,
      title: 'Hari 2 (12 Okt): Jungle Trekking Orangutan Bukit Lawang - Kembali ke Medan (Rp 1.200.000)',
      description: 'Jungle trekking Taman Nasional Gunung Leuser, melihat habitat Orangutan, river tubing, perjalanan kembali ke Medan & makan malam RM Garuda.',
      stops: [
        {
          destination_id: d1,
          title: 'Jungle Trekking Gunung Leuser & River Tubing Bukit Lawang',
          activity_description: 'Eksplorasi habitat orangutan Sumatera, menikmati buah tropis segar di hutan, dilanjutkan river tubing dan perjalanan ke Hotel Grand Mercure Medan.',
          estimated_minutes: 270,
        },
      ],
    },
    {
      day_number: 3,
      title: 'Hari 3 (13 Okt): Medan Heritage & Culinary City Tour (Rp 1.000.000)',
      description: 'Keliling objek wisata sejarah dan budaya Kota Medan: Maha Vihara Maitreya, Museum Negeri Sumut, Pos Bloc, Kesawan & Rumah Tjong A Fie.',
      stops: [
        {
          destination_id: d2,
          title: 'Museum Negeri Sumut, Pos Bloc & Tjong A Fie Mansion',
          activity_description: 'Kunjungan budaya ke Maha Vihara Maitreya, Museum Sumatera Utara, kuliner RM Batak, Seng Hap 1881 Kesawan, dan Rumah Tjong A Fie.',
          estimated_minutes: 300,
        },
      ],
    },
    {
      day_number: 4,
      title: 'Hari 4 (14 Okt): Medan - Parapat - Scenic Boat Cruising Samosir (Rp 1.300.000)',
      description: 'Berangkat pukul 07.00 WIB tembak ke Parapat, pelayaran kapal kayu ke Batu Gantung, Air Terjun Situmurun, Silimalombu, dan Huta Siallagan Samosir.',
      stops: [
        {
          destination_id: d0,
          title: 'Pelabuhan Parapat, Air Terjun Situmurun & Huta Siallagan',
          activity_description: 'Scenic boat cruising Danau Toba, makan siang Ecovillage Silimalombu, dan wisata budaya megalitikum Huta Siallagan di Samosir.',
          estimated_minutes: 300,
        },
      ],
    },
    {
      day_number: 5,
      title: 'Hari 5 (15 Okt): Eksplorasi Penuh Pulau Samosir & Kaldera Barat (Rp 1.300.000)',
      description: 'Keliling spot ikonik Samosir: Bukit Holbung, Air Terjun Efrata, Patung Yesus Sibea-bea, Air Panas Pangururan & Waterfront City.',
      stops: [
        {
          destination_id: d3,
          title: 'Bukit Holbung, Air Terjun Efrata & Sibea-bea Samosir',
          activity_description: 'Menikmati panorama perbukitan kaldera, air terjun alami, Jembatan Tano Ponggol, dan pertunjukan air mancur Waterfront Samosir.',
          estimated_minutes: 360,
        },
      ],
    },
    {
      day_number: 6,
      title: 'Hari 6 (16 Okt): Samosir - Parapat - Medan via Jalan Tol (Rp 1.300.000)',
      description: 'Belanja oleh-oleh khas Samosir, penyeberangan kembali, dan perjalanan lancar via Jalan Tol Tebing Tinggi menuju Medan / Bandara KNO.',
      stops: [
        {
          destination_id: d1,
          title: 'Samosir via Tol Trans Sumatera menuju Bandara KNO / Medan',
          activity_description: 'Perjalanan kembali melewati Jembatan Tano Ponggol / Ferry dan jalur bebas hambatan Tol menuju titik pengantaran akhir.',
          estimated_minutes: 240,
        },
      ],
    },
  ];
};

const loadSamosir1DTemplate = () => {
  const d0 = props.destinations[0]?.id || '';
  const d1 = props.destinations[1]?.id || d0;
  const d2 = props.destinations[2]?.id || d0;

  form.name = 'Paket Keliling Danau Toba & Samosir 1 Hari Full Dokumentasi';
  form.short_description = 'Perjalanan 1 Hari eksotis mengunjungi Air Terjun Efrata, Bukit Holbung, Bukit Cinta, Sibea-bea, Waterfront Samosir, dan Bukit Sidihoni.';
  form.full_description = 'Nikmati akhir pekan tak terlupakan mengelilingi spot paling ikonik di Danau Toba dan Pulau Samosir dalam 1 hari penuh. Sudah termasuk armada MPV/SUV bersih ber-AC dingin, BBM PP, Driver merangkap Guide lokal, uang makan & rokok supir, serta dokumentasi foto dan video.';
  form.tour_type = 'OPEN_TRIP';
  form.pricing_type = 'PER_PERSON';
  form.base_price = 270000;
  form.duration_days = 1;
  form.duration_nights = 0;
  form.min_participants = 4;
  form.max_participants = 14;
  form.includes_ferry = true;

  form.itinerary = [
    {
      day_number: 1,
      title: 'Hari 1: Eksplorasi Bukit Holbung, Efrata, Sibea-bea & Waterfront Samosir',
      description: 'Keberangkatan pagi menuju deretan destinasi panorama terbaik kaldera Danau Toba.',
      stops: [
        {
          destination_id: d0,
          title: props.destinations[0]?.name || 'Bukit Holbung & Air Terjun Efrata',
          activity_description: 'Trekking santai menikmati lanskap hijau Bukit Holbung dan kesegaran Air Terjun Efrata.',
          estimated_minutes: 120,
        },
        {
          destination_id: d1,
          title: props.destinations[1]?.name || 'Patung Sibea-bea & Jembatan Tano Ponggol',
          activity_description: 'Sesi foto panorama jalan berliku Sibea-bea dan ikon Jembatan Tano Ponggol.',
          estimated_minutes: 120,
        },
        {
          destination_id: d2,
          title: props.destinations[2]?.name || 'Waterfront Samosir, Air Panas Pangururan & Sidihoni',
          activity_description: 'Bersantai di Waterfront City Pangururan dan menikmati fenomena Danau di atas Danau Sidihoni.',
          estimated_minutes: 120,
        },
      ],
    },
  ];
};

const handleFileChange = (e) => {
  form.photos = Array.from(e.target.files);
};

const submitForm = () => {
  const combinedInclusions = [
    ...selectedPresets.value,
    ...(form.inclusions ? [form.inclusions] : []),
  ].join('\n• ');

  form.transform((data) => ({
    ...data,
    inclusions: combinedInclusions ? '• ' + combinedInclusions : '',
  })).post(route('admin.tours.store'), {
    forceFormData: true,
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Buat Paket Wisata Resmi Platform — Admin Console" />

    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 mb-6 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 text-[11px] font-bold text-primary uppercase tracking-wider mb-1">
          <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
          <span>Super Admin Curated Tour Builder</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
          Susun Paket Wisata Resmi Platform
        </h1>
        <p class="text-xs text-slate-500 mt-1">
          Paket yang dibuat di sini berstatus <span class="font-bold text-slate-700">RESMI PLATFORM (INTERNAL)</span>, otomatis terikat dengan foto galeri destinasi yang dipilih, dan siap di-dispatch ke mitra armada.
        </p>
      </div>

      <!-- Quick Reference Template Loaders -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          @click="loadFamtrip6D5NTemplate"
          class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200/80 text-xs font-bold transition"
        >
          ⚡ Muat Template: 6D5N Famtrip Sumut
        </button>
        <button
          type="button"
          @click="loadSamosir1DTemplate"
          class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200/80 text-xs font-bold transition"
        >
          ⚡ Muat Template: 1D Danau Toba (Rp 270rb)
        </button>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Section 1: Basic Info & Pricing -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-2xs space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-900">1. Informasi Utama & Skema Harga Paket</h2>
            <p class="text-[11px] text-slate-500">Mendukung terjemahan otomatis Bahasa Indonesia & Bahasa Inggris</p>
          </div>
          <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-bold">
            <button
              type="button"
              @click="activeLang = 'id'"
              :class="activeLang === 'id' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500'"
              class="px-3 py-1 rounded-lg transition"
            >
              🇮🇩 Bahasa Indonesia
            </button>
            <button
              type="button"
              @click="activeLang = 'en'"
              :class="activeLang === 'en' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500'"
              class="px-3 py-1 rounded-lg transition"
            >
              🇬🇧 English (Opsional / Auto)
            </button>
          </div>
        </div>

        <div v-show="activeLang === 'id'" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Paket Wisata *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Misal: 6D5N Famtrip North Sumatra: KNO - Bukit Lawang - Medan - Danau Toba"
              class="w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-primary focus:ring-primary"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Ringkasan Singkat (Highlight Paket) *</label>
            <input
              v-model="form.short_description"
              type="text"
              required
              maxlength="300"
              placeholder="Termasuk armada bersih ber-AC, BBM PP, makan & rokok supir, serta tiket ferry terjadwal."
              class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Deskripsi Lengkap Paket Wisata *</label>
            <textarea
              v-model="form.full_description"
              rows="3"
              required
              placeholder="Jelaskan detail pengalaman perjalanan, keunggulan rute, dan kenyamanan armada..."
              class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
            ></textarea>
          </div>
        </div>

        <div v-show="activeLang === 'en'" class="space-y-4 text-xs">
          <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-800 text-[11px]">
            💡 Jika dikosongkan, sistem akan menerjemahkan otomatis dari Bahasa Indonesia ke Bahasa Inggris saat disimpan.
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Package Title (EN)</label>
            <input
              v-model="form.name_en"
              type="text"
              placeholder="Auto-translated if left blank"
              class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
            />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Short Summary (EN)</label>
            <input
              v-model="form.short_description_en"
              type="text"
              placeholder="Auto-translated if left blank"
              class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
            />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Full Description (EN)</label>
            <textarea
              v-model="form.full_description_en"
              rows="3"
              placeholder="Auto-translated if left blank"
              class="w-full rounded-xl border-slate-300 text-xs focus:border-primary focus:ring-primary"
            ></textarea>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Kategori Trip *</label>
            <select v-model="form.tour_type" class="w-full rounded-xl border-slate-300 text-xs font-semibold">
              <option v-for="t in tourTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Skema Hitungan Harga *</label>
            <select v-model="form.pricing_type" class="w-full rounded-xl border-slate-300 text-xs font-semibold">
              <option value="PER_PERSON">Per Orang (Open Trip / Pax)</option>
              <option value="PER_GROUP">Per Rombongan / Unit Armada (Private)</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Harga Dasar Paket (Rp) *</label>
            <input
              v-model="form.base_price"
              type="number"
              min="10000"
              required
              class="w-full rounded-xl border-slate-300 text-xs font-extrabold tabular-nums"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Mitra Default (Opsional)</label>
            <select v-model="form.partner_id" class="w-full rounded-xl border-slate-300 text-xs">
              <option value="">-- Dispatch Saat Ada Booking --</option>
              <option v-for="p in partners" :key="p.id" :value="p.id">
                {{ p.business_name }} (★ {{ p.average_rating.toFixed(1) }})
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Durasi Hari *</label>
            <input v-model="form.duration_days" type="number" min="1" class="w-full rounded-xl border-slate-300 text-xs" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Durasi Malam *</label>
            <input v-model="form.duration_nights" type="number" min="0" class="w-full rounded-xl border-slate-300 text-xs" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Min. Peserta *</label>
            <input v-model="form.min_participants" type="number" min="1" class="w-full rounded-xl border-slate-300 text-xs" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Maks. Peserta *</label>
            <input v-model="form.max_participants" type="number" min="1" class="w-full rounded-xl border-slate-300 text-xs" />
          </div>
        </div>
      </div>

      <!-- Section 2: Multi-Day Itinerary & Master Destination Auto-Binding -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-2xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
          <div>
            <h2 class="text-sm font-bold text-slate-900">
              2. Rute Harian (Itinerary) & Binding Galeri Destinasi Wisata
            </h2>
            <p class="text-[11px] text-slate-500">
              Pilih objek wisata pada setiap titik kunjungan agar foto-foto dari lokasi tersebut otomatis tampil di halaman paket wisata ini.
            </p>
          </div>
          <button
            type="button"
            @click="addDay"
            class="px-3.5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-dark transition shrink-0"
          >
            + Tambah Hari Perjalanan
          </button>
        </div>

        <!-- Bound Destinations Photo Strip Preview -->
        <div v-if="boundDestinationsPreview.length > 0" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
          <div class="flex items-center justify-between mb-2.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">
              🔗 {{ boundDestinationsPreview.length }} Destinasi Terikat Otomatis ke Galeri Paket Ini:
            </span>
            <span class="text-[11px] text-slate-500">Wisatawan dapat melihat langsung foto asli lokasi tujuan</span>
          </div>
          <div class="flex items-center gap-3 overflow-x-auto pb-1">
            <div
              v-for="dest in boundDestinationsPreview"
              :key="dest.id"
              class="flex items-center gap-2.5 p-2 rounded-xl bg-white border border-slate-200 shrink-0 shadow-2xs"
            >
              <img :src="dest.image_url" :alt="dest.name" class="w-12 h-12 rounded-lg object-cover" />
              <div class="pr-2">
                <div class="text-xs font-bold text-slate-900">{{ dest.name }}</div>
                <div class="text-[10px] text-primary font-semibold">{{ dest.cluster }} • {{ dest.media_count }} Foto</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Days Loop -->
        <div class="space-y-4">
          <div
            v-for="(day, dayIdx) in form.itinerary"
            :key="dayIdx"
            class="p-5 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-4"
          >
            <div class="flex items-center justify-between">
              <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-900 text-white text-xs font-bold">
                Hari ke-{{ day.day_number }}
              </span>
              <button
                v-if="form.itinerary.length > 1"
                type="button"
                @click="removeDay(dayIdx)"
                class="text-xs font-bold text-rose-600 hover:underline"
              >
                Hapus Hari Ini
              </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
              <div>
                <label class="block font-bold text-slate-700 mb-1">Judul Rute & Estimasi Biaya Harian *</label>
                <input
                  v-model="day.title"
                  type="text"
                  required
                  placeholder="Misal: Hari 1: KNO - Bukit Lawang (Biaya: 1.200.000 include makan/rokok supir)"
                  class="w-full rounded-xl border-slate-300 text-xs font-semibold"
                />
              </div>
              <div>
                <label class="block font-bold text-slate-700 mb-1">Catatan Harian</label>
                <input
                  v-model="day.description"
                  type="text"
                  placeholder="Ringkasan perjalanan dan jadwal makan/istirahat..."
                  class="w-full rounded-xl border-slate-300 text-xs"
                />
              </div>
            </div>

            <!-- Stops inside Day -->
            <div class="space-y-3 pl-3 border-l-2 border-primary/30">
              <div
                v-for="(stop, stopIdx) in day.stops"
                :key="stopIdx"
                class="p-3.5 rounded-xl bg-white border border-slate-200/80 space-y-3 text-xs shadow-2xs"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-slate-700">Titik Kunjungan (Stop #{{ stopIdx + 1 }})</span>
                  <button
                    v-if="day.stops.length > 1"
                    type="button"
                    @click="removeStop(dayIdx, stopIdx)"
                    class="text-[11px] text-rose-600 font-semibold hover:underline"
                  >
                    Hapus Stop
                  </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <div>
                    <label class="block font-bold text-primary mb-1">Pilih Destinasi Master (Binding Foto)</label>
                    <select
                      v-model="stop.destination_id"
                      @change="onSelectDestination(stop)"
                      class="w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-primary focus:ring-primary"
                    >
                      <option value="">-- Rute Bebas / Non-Destinasi --</option>
                      <option v-for="dest in destinations" :key="dest.id" :value="dest.id">
                        📍 {{ dest.name }} ({{ dest.cluster }})
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Kegiatan / Lokasi *</label>
                    <input
                      v-model="stop.title"
                      type="text"
                      required
                      placeholder="Misal: Bukit Holbung / Air Terjun Efrata"
                      class="w-full rounded-xl border-slate-300 text-xs font-semibold"
                    />
                  </div>

                  <div>
                    <label class="block font-bold text-slate-700 mb-1">Durasi Kunjungan (Menit)</label>
                    <input
                      v-model="stop.estimated_minutes"
                      type="number"
                      min="15"
                      class="w-full rounded-xl border-slate-300 text-xs"
                    />
                  </div>
                </div>

                <div>
                  <label class="block font-bold text-slate-700 mb-1">Deskripsi Aktivitas di Lokasi</label>
                  <input
                    v-model="stop.activity_description"
                    type="text"
                    placeholder="Menikmati panorama kaldera, foto dokumentasi, dan bersantai..."
                    class="w-full rounded-xl border-slate-300 text-xs"
                  />
                </div>

                <!-- Live Bound Destination Preview inside Stop -->
                <div
                  v-if="getDestinationById(stop.destination_id)"
                  class="p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-200/70 flex items-center justify-between gap-3"
                >
                  <div class="flex items-center gap-2.5">
                    <img
                      :src="getDestinationById(stop.destination_id).image_url"
                      class="w-10 h-10 rounded-lg object-cover border border-emerald-200"
                    />
                    <div>
                      <div class="text-[11px] font-bold text-emerald-950">
                        Terikat ke: {{ getDestinationById(stop.destination_id).name }}
                      </div>
                      <div class="text-[10px] text-emerald-700">
                        {{ getDestinationById(stop.destination_id).media_count }} foto galeri lokasi ini otomatis ditampilkan pada halaman paket.
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <button
                type="button"
                @click="addStop(dayIdx)"
                class="text-xs font-bold text-primary hover:underline inline-flex items-center gap-1"
              >
                + Tambah Titik Kunjungan di Hari ke-{{ day.day_number }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 3: Preset Inclusions & Terms -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-2xs space-y-5 text-xs">
        <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">
          3. Standar Fasilitas Termasuk (Preset Inclusions Sumatera Utara) & Jaminan
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
          <label
            v-for="item in presetInclusionOptions"
            :key="item"
            class="flex items-start gap-2.5 p-3 rounded-xl border cursor-pointer transition"
            :class="selectedPresets.includes(item) ? 'bg-primary/5 border-primary text-slate-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600'"
          >
            <input
              type="checkbox"
              :value="item"
              v-model="selectedPresets"
              class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary"
            />
            <span class="text-[11px] leading-snug">{{ item }}</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Fasilitas Tambahan Lainnya</label>
            <textarea
              v-model="form.inclusions"
              rows="2"
              placeholder="Tambahkan fasilitas lain jika ada..."
              class="w-full rounded-xl border-slate-300 text-xs"
            ></textarea>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Tidak Termasuk (Exclusions)</label>
            <textarea
              v-model="form.exclusions"
              rows="2"
              class="w-full rounded-xl border-slate-300 text-xs"
            ></textarea>
          </div>
        </div>

        <div class="pt-2">
          <label class="block font-bold text-slate-700 mb-1">Unggah Foto Sampul Tambahan (Opsional — Otomatis memakai foto destinasi jika kosong)</label>
          <input
            type="file"
            multiple
            accept="image/jpeg,image/png,image/webp"
            @change="handleFileChange"
            class="text-xs text-slate-600"
          />
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
          <Link
            href="/admin"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold shadow-sm transition"
          >
            Terbitkan Paket Wisata Resmi
          </button>
        </div>
      </div>
    </form>
  </AdminLayout>
</template>

