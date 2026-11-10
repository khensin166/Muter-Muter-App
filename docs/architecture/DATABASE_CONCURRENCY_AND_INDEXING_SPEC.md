# Database Concurrency, Race Condition Prevention & Indexing Specification
**Muter-Muter Travel Marketplace (North Sumatra)**

Dokumen ini menjelaskan strategi teknis arsitektur database dalam mencegah **race conditions**, penanganan **locking concurrency**, serta **registry indeks database PostgreSQL** untuk menjamin kestabilan performa transaksi tinggi.

---

## 1. Analisis Skenario Konkurensi & Pencegahan Race Condition

### Skenario 1: Hold Expiration Cron vs. Admin Payment Approval
- **Masalah Potensial:**
  Customer membayar DP di menit ke-23 jam 59 menit. Admin sedang mereview bukti transfer dan menekan tombol *Approve* tepat bersamaan ketika jadwal background cron job `inventory:release-expired-holds` dieksekusi. Jika terjadi *read-modify-write* tanpa locking, cron dapat menandai booking sebagai `EXPIRED` dan melepas kuota tepat setelah admin menandai pesanan sebagai `CONFIRMED`.
- **Solusi Arsitektural (Pessimistic Locking `SELECT ... FOR UPDATE`):**
  1. Pada `AdminPaymentController::approve()`, pembaruan dibungkus dalam `DB::transaction(...)` dengan pessimistic lock:
     ```php
     $booking = Booking::where('id', $payment->booking_id)->lockForUpdate()->firstOrFail();
     ```
  2. Pada background cron `ReleaseExpiredInventoryHolds::handle()`, setiap rilis hold diperiksa dengan pessimistic lock:
     ```php
     DB::transaction(function () use ($hold) {
         $booking = Booking::where('id', $hold->booking_id)->lockForUpdate()->first();
         if ($booking && in_array($booking->booking_status, [BookingStatus::CONFIRMED, BookingStatus::COMPLETED])) {
             $hold->status = InventoryStatus::ALLOCATED;
             $hold->save();
             return; // Batal expire, karena telah dikonfirmasi sah
         }
         // Jika belum dibayar, ubah menjadi EXPIRED & RELEASED
     });
     ```
  3. **Idempotensi Approval:** Jika `$booking->booking_status === CONFIRMED` dan `$payment->status === VERIFIED`, controller langsung mengembalikan respons sukses tanpa memicu perubahan ulang.

---

### Skenario 2: Double Approval atau Rapid Clicks oleh Admin
- **Masalah Potensial:**
  Admin tanpa sengaja menekan tombol "Verifikasi Pembayaran" secara cepat dua kali (double click), atau dua orang staf admin memproses verifikasi bukti transfer yang sama pada detik yang sama.
- **Solusi Arsitektural:**
  1. Transaksi dibungkus dalam `DB::transaction`.
  2. Pengecekan status awal di dalam lock: jika status pembayaran sudah `VERIFIED`, mutasi kedua otomatis dibatalkan tanpa melempar duplikasi settlement vendor atau status history.
  3. Relasi `VendorSettlement` menggunakan `firstOrCreate(['booking_id' => $booking->id], ...)` sehingga settlement payout mitra dijamin *unique* per transaksi.

---

### Skenario 3: Booking Kuota Open Trip & Armada Rental Secara Bersamaan
- **Masalah Potensial:**
  Dua customer berbeda memesan kursi terakhir pada jadwal keberangkatan Open Trip yang sama (`tour_departures`) atau mobil rental yang sama pada tanggal yang identik.
- **Solusi Arsitektural:**
  1. Penguncian baris keberangkatan tur / armada dengan `lockForUpdate()`.
  2. Pengecekan kuota tersisa `(total_capacity - booked_seats - held_seats) >= requested_seats`.
  3. Pembuatan `InventoryHold` dengan batas waktu kedaluwarsa eksplisit (`expires_at = now()->addHours(24)`).
  4. Bila kuota tidak mencukupi, transaksi di-rollback secara otomatis dan sistem melempar validasi `Stok armada / kuota tur tidak mencukupi`.

---

### Skenario 4: Agregasi Metrik Mitra & Penghitungan Trip Selesai
- **Masalah Potensial:**
  Saat cron `trips:mark-completed` berjalan setiap malam, trip yang telah berakhir ditandai sebagai `COMPLETED`. Jika counter dihitung melalui `count()` manual tanpa atomisitas, data metrik mitra bisa tidak konsisten jika ada transaksi bersamaan.
- **Solusi Arsitektural:**
  1. Menggunakan atomic SQL update:
     ```php
     Partner::where('id', $booking->partner_id)->increment('completed_trips_count');
     ```
  2. Kolom `completed_trips_count` memiliki default `0` dan diindeks secara komposit bersama `average_rating`.

---

## 2. Registry Indeks Database PostgreSQL

Indeks ditambahkan melalui migrasi `database/migrations/2026_11_10_210000_add_performance_and_concurrency_indexes.php` untuk mengoptimalkan performa pencarian, penyaringan, dan pencegahan *Full Table Scan*:

| Tabel | Nama Indeks | Kolom yang Diindeks | Tujuan & Query Terkait |
|---|---|---|---|
| `partners` | `partners_status_rating_idx` | `(status, average_rating)` | Menampilkan daftar mitra aktif dengan reputasi tertinggi di portal / direktori. |
| `partners` | `partners_completed_trips_idx` | `(completed_trips_count)` | Filter mitra terpercaya berdasarkan jam terbang / trip sukses. |
| `tour_itinerary_stops` | `itinerary_stops_dest_id_idx` | `(destination_id)` | Binding foto objek wisata ke paket tur secara instan (JOIN agregasi galeri). |
| `tour_itinerary_stops` | `itinerary_stops_day_order_idx` | `(tour_itinerary_day_id, stop_order)` | Mengurutkan rute perjalanan harian tanpa filesort. |
| `destination_media` | `dest_media_lookup_idx` | `(destination_id, is_primary, sort_order)` | Mengambil thumbnail utama dan galeri objek wisata secara cepat. |
| `destinations` | `destinations_cluster_featured_idx` | `(cluster, is_featured)` | Filter destinasi beranda berdasarkan klaster kawasan (Parapat, Samosir, dll). |
| `products` | `products_ownership_lookup_idx` | `(ownership_type, status, approval_status)` | Filter paket tur resmi platform (`INTERNAL`) vs produk mitra (`PARTNER`). |
| `bookings` | `bookings_completion_cron_idx` | `(booking_status, service_end_date)` | Eksekusi cepat cron `trips:mark-completed` untuk rilis payout mitra. |
| `bookings` | `bookings_unmasking_cron_idx` | `(booking_status, service_start_date, contact_unmasked)` | Eksekusi cron unmasking kontak supir H-12 jam. |
| `inventory_holds` | `holds_vehicle_concurrency_idx` | `(vehicle_id, status, expires_at)` | Verifikasi ketersediaan armada mobil rental dan pembersihan hold kedaluwarsa. |
| `inventory_holds` | `holds_tour_concurrency_idx` | `(tour_departure_id, status, expires_at)` | Verifikasi sisa kuota kursi Open Trip dan pembersihan hold kedaluwarsa. |
| `destinations` | `destinations_name_translations_gin` | `GIN (name)` | Pencarian multibahasa berbasis JSONB (bahasa Indonesia & Inggris). |
| `products` | `products_name_translations_gin` | `GIN (name)` | Pencarian nama produk multibahasa berbasis JSONB. |

---

## 3. Best Practices Transaksi Database untuk Developer

Setiap penambahan fitur baru yang memodifikasi status booking, saldo payout, atau alokasi kuota wajib mematuhi panduan berikut:

1. **Selalu Gunakan `DB::transaction()`**:
   Operasi lintas tabel (misal: Booking + Payment + InventoryHold + Settlement) TIDAK BOLEH dilakukan terpisah.
2. **Pessimistic Lock Sebelum Validasi Bisnis**:
   Gunakan `$model = Model::where(...)->lockForUpdate()->first()` di awal transaksi sebelum memeriksa kondisi logika (`if ($model->status === ...)`).
3. **Atomic Operations untuk Counter**:
   Gunakan `$model->increment('column')` atau `$model->decrement('column')` daripada `$model->column = $model->column + 1`.
4. **Indeks Foreign Key Secara Eksplisit**:
   PostgreSQL secara default **tidak membuat indeks pada Foreign Key constraints**. Pastikan setiap FK yang sering di-JOIN selalu memiliki indeks B-Tree.

