# Rangkuman Hasil Grooming — Lake Toba Travel Marketplace
**Versi Dokumen:** 1.2 (Final Grooming Approved)  
**Tanggal:** 5 November 2026  
**Status:** Semua Keputusan Terkunci & Disetujui (Ready for Documentation & Implementation Plan)

---

## 1. Ringkasan Visi & Model Bisnis
* **Visi Produk:** *"One platform to discover, plan, and experience North Sumatra."*
* **Target Wilayah Awal (MVP):** Kawasan Danau Toba (Parapat, Samosir East, Samosir West, Merek/Tongging, dengan Medan sebagai hub penjemputan/keberangkatan).
* **Model Bisnis:** Hybrid Marketplace:
  * **Internal Products:** Produk milik/dikelola langsung oleh platform.
  * **Partner Products:** Produk dari mitra/vendor lokal terverifikasi.
* **Tipe Produk:**
  1. `TOUR` (One Day Trip, Multi-day, Open Trip, Private Trip, Custom Trip).
  2. `VEHICLE` (Sewa mobil, minibus, bus wisata, transfer service).
* **Ferry Danau Toba:** Tidak dijual sebagai tiket mandiri lepas. Penyeberangan ferry terintegrasi sebagai bagian dari *itinerary / inclusion* pada paket wisata (`TOUR`).

---

## 2. Seluruh Keputusan Grooming yang Telah Disepakati

### A. Lifecycle & SLA Pemesanan (Booking)
1. **Vendor Response SLA:** Maksimal **24 jam** sejak booking dibuat (`PENDING_APPROVAL`).
2. **Timeout Handling:** Jika vendor tidak merespons dalam 24 jam, booking **tidak di-expire otomatis**, melainkan dialihkan ke antrean **intervensi manual Admin** untuk difollow-up atau dipindahkan unit/vendornya.
3. **Mekanisme Approval:**
   * Cukup **Vendor Approval** untuk memvalidasi ketersediaan dan memicu status `AWAITING_PAYMENT` (customer dapat melanjutkan ke pembayaran DP).
   * Admin memiliki visibilitas penuh (*dashboard monitoring*) dan hak intervensi jika terjadi kendala/anomali.
4. **Minimum Booking Lead Time:**
   * Paket Tour: **H-1 (24 jam)** sebelum jadwal keberangkatan.
   * Rental Kendaraan: **H-12 jam** sebelum waktu sewa dimulai.
5. **Temporary Inventory Hold:**
   * Diberikan durasi hold **24 jam** setelah booking disetujui untuk pembayaran DP awal. Jika lewat 24 jam belum dibayar, hold dilepas.

### B. Down Payment (DP) & Skema Pembayaran
1. **Konfigurasi DP:**
   * Diatur fleksibel oleh Admin dengan nilai **default sistem (30%)**.
   * Admin dapat menyesuaikan persentase DP per produk atau kebijakan operasional.
2. **Alur Pembayaran MVP (Opsi A — Semi-Automated):**
   * Customer mengisi form booking di web $\rightarrow$ Vendor konfirmasi ketersediaan $\rightarrow$ Sistem menampilkan rincian tagihan DP dan nomor rekening bank platform di web, serta otomatis mengirimkan notifikasi via Go WhatsApp Gateway $\rightarrow$ Customer transfer dan mengunggah bukti transfer di web (atau konfirmasi ke admin via WA) $\rightarrow$ Admin memverifikasi bukti transfer $\rightarrow$ Booking menjadi `CONFIRMED`.
3. **Pelunasan:**
   * Sisa pembayaran (balance) dapat dilunasi hingga hari keberangkatan (H-Day).
   * Status Booking bisa `CONFIRMED` meskipun status Payment masih `PARTIALLY_PAID`.

### C. Kebijakan Pembatalan & Pengembalian Dana (Cancellation & Refund)
1. **Tingkat Pengembalian Dana (Default Baseline MVP):**
   * Pembatalan **> 7 hari** sebelum keberangkatan: **Refund 100%**
   * Pembatalan **3 – 7 hari** sebelum keberangkatan: **Refund 50%**
   * Pembatalan **< 3 hari** sebelum keberangkatan: **Refund 0% (Hangus)**
2. **Dasar Kalkulasi Refund:**
   * Dihitung berdasarkan nominal yang **sudah dibayarkan secara riil** (uang masuk DP/pelunasan), bukan dari total nilai pemesanan.
3. **Pembatalan oleh Vendor:**
   * Customer berhak memilih **Full Refund (100%)** atau opsi **Reschedule**.

### D. Komisi & Settlement Vendor
1. **Komisi Platform:** Default **10%** dari harga produk (disimpan snapshot komisi saat booking dibuat).
2. **Settlement Mitra:** Eligible dicairkan setelah status pesanan menjadi `COMPLETED` (layanan selesai tanpa sengketa).

### E. Struktur Mitra & Verifikasi
1. **Akun Multi-Staff (2 Level Akses):**
   * **Partner Owner:** Mengelola profil bisnis, rekening pencairan dana, manajemen staf, dan manajemen produk/armada.
   * **Partner Staff:** Fokus mengonfirmasi ketersediaan booking (`Approve / Reject`) dan memantau jadwal operasional harian.
2. **Dokumen Wajib Verifikasi:**
   * KTP Penanggung Jawab.
   * Izin Usaha / NIB / Surat Keterangan Usaha.
   * Foto Unit Kendaraan / Legalitas Armada / Kelayakan Operasional.

### F. Notifikasi & Integrasi WhatsApp
* Disediakan modul modular di Laravel (`WhatsAppService`) yang terhubung dengan **Go WhatsApp Gateway API** milik internal.
* Konfigurasi (Endpoint URL, Auth Token/Header, Sender ID) dikelola melalui `.env` sehingga fleksibel saat integrasi final.

### G. Arsitektur & Teknologi
* **Stack:** Laravel 11, Vue 3, Inertia.js, Tailwind CSS, PostgreSQL.
* **Arsitektur:** Modular Monolith (satu repositori).
* **Identitas & Uang:** UUID v4 untuk Primary Key, `NUMERIC(14,2)` untuk nominal uang, `TIMESTAMPTZ` untuk waktu.
