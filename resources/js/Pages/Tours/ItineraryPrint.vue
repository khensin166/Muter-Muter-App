<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { formatRupiah as formatRupiahHelper } from '@/Composables/useFormat'

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
})

const printDocument = () => {
  window.print()
}

const formatRupiah = formatRupiahHelper
</script>

<template>
  <Head :title="`Itinerary Resmi - ${product.name} | Beta Tudia - Muter-Muter`" />

  <div class="min-h-screen bg-slate-100 py-6 px-4 sm:px-6 print:p-0 print:bg-white text-slate-900 font-sans">
    <!-- Top Action Toolbar (Hidden in Print) -->
    <div class="max-w-4xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
      <div class="flex items-center gap-3">
        <Link
          :href="`/tours/${product.slug}`"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
        >
          <span>←</span>
          <span>Kembali ke Paket Wisata</span>
        </Link>
        <span class="text-xs text-slate-500 hidden sm:inline">
          Pratinjau Dokumen Resmi Rencana Perjalanan
        </span>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="printDocument"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-extrabold shadow-md hover:shadow-lg transition cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
          </svg>
          <span>Cetak / Simpan ke PDF</span>
        </button>
      </div>
    </div>

    <!-- Official Printable Document Container (A4 Page Fit) -->
    <main class="max-w-4xl mx-auto bg-white p-8 sm:p-12 print:p-0 rounded-3xl shadow-xl print:shadow-none border border-slate-200 print:border-none space-y-8">
      
      <!-- Batak Gorga Motif Ribbon Header Accent -->
      <div class="h-2 w-full rounded-full bg-gradient-to-r from-red-700 via-amber-500 via-slate-900 to-red-700 print:h-2.5"></div>

      <!-- Header Section: Beta Tudia Brand & Platform Authority -->
      <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b-2 border-slate-900">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded-md bg-red-700 text-white text-[11px] font-black uppercase tracking-widest">
              Beta Tudia!
            </span>
            <span class="text-xs font-extrabold text-slate-800 tracking-wider uppercase">
              Sumatera Utara Tour Itinerary
            </span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-950 tracking-tight leading-tight">
            {{ product.name }}
          </h1>
          <p class="text-xs text-slate-600 mt-1">
            Dokumen resmi rencana perjalanan & spesifikasi fasilitas terkurasi platform Muter-Muter App.
          </p>
        </div>

        <!-- Official Stamp Badge -->
        <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
          <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-[11px] font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse print:hidden"></span>
            <span>VERIFIED ITINERARY</span>
          </div>
          <span class="text-[10px] text-slate-500 mt-1 font-mono">
            REF: {{ product.slug.toUpperCase().slice(0, 18) }}
          </span>
        </div>
      </header>

      <!-- Trip Specifications Grid -->
      <section class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
        <div>
          <span class="text-[10px] uppercase font-bold text-slate-600 block">Durasi Wisata</span>
          <span class="font-extrabold text-slate-900 text-sm">
            {{ product.tour.duration_days }} Hari {{ product.tour.duration_nights }} Malam
          </span>
        </div>
        <div>
          <span class="text-[10px] uppercase font-bold text-slate-600 block">Kategori Rute</span>
          <span class="font-extrabold text-slate-900 text-sm">
            {{ product.tour.tour_type || 'Private & Curated' }}
          </span>
        </div>
        <div>
          <span class="text-[10px] uppercase font-bold text-slate-600 block">Mulai Harga</span>
          <span class="font-extrabold text-primary text-sm">
            {{ formatRupiah(product.base_price) }}
            <span class="text-[10px] font-normal text-slate-500">/ pax</span>
          </span>
        </div>
        <div>
          <span class="text-[10px] uppercase font-bold text-slate-600 block">Tiket Ferry Samosir</span>
          <span class="font-extrabold text-emerald-700 text-sm">
            {{ product.tour.includes_ferry ? '✅ Terjadwal Resmi PP' : 'Rute Darat Tele' }}
          </span>
        </div>
      </section>

      <!-- 5 Golden Guarantees Summary Box (Brand Promise) -->
      <section class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 text-xs space-y-2">
        <div class="flex items-center gap-2 text-emerald-900 font-extrabold text-xs">
          <span>🛡️</span>
          <span>5 Standar Emas & Jaminan Kenyamanan Beta Tudia — Muter-Muter:</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] text-emerald-900">
          <div class="flex items-center gap-1.5">
            <span class="text-emerald-600 font-bold">✓</span>
            <span>Armada Bersih, Wangi & AC Dingin</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-emerald-600 font-bold">✓</span>
            <span>Garansi Mobil Pengganti 1–2 Jam</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-emerald-600 font-bold">✓</span>
            <span>Include Makan & Uang Rokok Supir</span>
          </div>
        </div>
      </section>

      <!-- Day by Day Detailed Itinerary Breakdown -->
      <section class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
          <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wide flex items-center gap-2">
            <span>📅</span>
            <span>Rincian Rencana Perjalanan Harian (Itinerary)</span>
          </h2>
          <span class="text-[11px] font-medium text-slate-600">
            Total {{ product.itinerary ? product.itinerary.length : 0 }} Hari Kegiatan
          </span>
        </div>

        <div v-if="product.itinerary && product.itinerary.length > 0" class="space-y-6">
          <div
            v-for="day in product.itinerary"
            :key="day.id"
            class="rounded-2xl border border-slate-200 overflow-hidden break-inside-avoid"
          >
            <!-- Day Header Banner -->
            <div class="bg-slate-900 text-white px-5 py-3 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-lg bg-red-600 text-white text-xs font-black flex items-center justify-center shrink-0">
                  H{{ day.day_number }}
                </span>
                <span class="font-extrabold text-sm tracking-wide">
                  {{ day.title }}
                </span>
              </div>
              <span class="text-[11px] text-slate-300 hidden sm:inline">
                Hari ke-{{ day.day_number }}
              </span>
            </div>

            <!-- Day Description (if any) -->
            <div v-if="day.description" class="px-5 pt-3 text-xs text-slate-600 italic bg-slate-50/50">
              "{{ day.description }}"
            </div>

            <!-- Stops Table / List -->
            <div class="p-5 space-y-3">
              <div
                v-for="stop in day.stops"
                :key="stop.id"
                class="flex items-start gap-4 p-3 rounded-xl bg-slate-50/80 border border-slate-100 text-xs"
              >
                <!-- Time Badge -->
                <div class="w-24 shrink-0 font-mono font-bold text-slate-700 pt-0.5">
                  ⏱️ {{ stop.time_schedule || 'Sesuai Jadwal' }}
                </div>

                <!-- Stop Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="font-extrabold text-slate-950 text-sm">{{ stop.title }}</span>
                    <span
                      v-if="stop.destination_cluster"
                      class="px-2 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-bold"
                    >
                      {{ stop.destination_cluster }}
                    </span>
                  </div>
                  <p v-if="stop.description" class="text-xs text-slate-600 mt-1 leading-relaxed">
                    {{ stop.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-xs text-slate-500 italic p-6 text-center border border-dashed border-slate-200 rounded-xl">
          Rincian itinerary harian belum dikonfigurasi untuk paket ini.
        </div>
      </section>

      <!-- Inclusions & Exclusions Grid -->
      <section class="grid grid-cols-1 sm:grid-cols-2 gap-4 break-inside-avoid">
        <div class="p-5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 space-y-2.5">
          <h3 class="text-xs font-black text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
            <span>✅</span>
            <span>Fasilitas Termasuk (All-Inclusive)</span>
          </h3>
          <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed font-medium">
            {{ product.tour.inclusions || '• Armada Nyaman, Bersih & AC Dingin\n• BBM Pulang-Pergi Penuh\n• Driver merangkap Tour Guide Ramah\n• Makan & Uang Rokok Supir (Bebas Pungli)\n• Tiket Ferry Danau Toba PP Berjadwal\n• Air Mineral Harian' }}
          </p>
        </div>

        <div class="p-5 rounded-2xl bg-rose-50/50 border border-rose-200/80 space-y-2.5">
          <h3 class="text-xs font-black text-rose-900 uppercase tracking-wider flex items-center gap-1.5">
            <span>❌</span>
            <span>Tidak Termasuk (Exclusions)</span>
          </h3>
          <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed font-medium">
            {{ product.tour.exclusions || '• Tiket Pesawat menuju/dari Bandara Kualanamu (KNO) atau Silangit (DTB)\n• Pengeluaran belanja pribadi & oleh-oleh\n• Asuransi perjalanan opsional tambahan' }}
          </p>
        </div>
      </section>

      <!-- Important Notes & SLA Synchronization -->
      <section class="p-5 rounded-2xl bg-slate-900 text-white text-xs space-y-3 break-inside-avoid">
        <div class="font-extrabold text-sm flex items-center gap-2 text-amber-300">
          <span>ℹ️</span>
          <span>Ketentuan Penting & Prosedur Penjemputan:</span>
        </div>
        <ul class="space-y-1.5 text-slate-200 text-[11px] leading-relaxed">
          <li class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">•</span>
            <span><strong>Batas Pemesanan (Lead Time):</strong> Minimal H-1 (24 jam sebelum keberangkatan) untuk persiapan armada dan konfirmasi jadwal ferry.</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">•</span>
            <span><strong>Sinkronisasi Kontak Driver:</strong> Nama supir, nomor WhatsApp, dan plat nomor kendaraan dirilis secara otomatis H-12 jam sebelum keberangkatan demi keamanan privasi dan kenyamanan koordinasi penjemputan bandara.</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">•</span>
            <span><strong>Skema Pembayaran Transparan:</strong> Down Payment (DP) 30% mengunci unit dan kuota kursi pasti. Sisa pelunasan dibayarkan saat bertemu driver di titik awal penjemputan.</span>
          </li>
        </ul>
      </section>

      <!-- Footer Sign-off & Verification -->
      <footer class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500 break-inside-avoid">
        <div>
          <div class="font-extrabold text-slate-800 text-xs">Beta Tudia — Muter-Muter Travel Marketplace</div>
          <div>Dikelola resmi oleh Platform Regional Terintegrasi Sumatera Utara</div>
          <div class="text-[10px] text-slate-600 mt-0.5">Bantuan & Layanan Wisatawan: WhatsApp Hotline 0813-7788-9900</div>
        </div>

        <div class="text-center sm:text-right">
          <div class="font-mono text-[10px] text-slate-600">Dicetak pada: {{ new Date().toLocaleDateString('id-ID', { dateStyle: 'full' }) }}</div>
          <div class="text-emerald-700 font-bold text-[10px] mt-0.5">✓ Dokumen Resmi Terverifikasi Sistem</div>
        </div>
      </footer>
    </main>
  </div>
</template>

<style>
@media print {
  body {
    background-color: white !important;
  }
  @page {
    size: A4;
    margin: 12mm 15mm;
  }
}
</style>

