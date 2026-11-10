# Database Migration & Seeding Deployment Guide
**Muter-Muter Travel Marketplace (North Sumatra)**

Dokumen ini adalah panduan resmi eksekusi database (migrasi skema, seeding baseline data, dan backfill multibahasa) untuk lingkungan **Lokal**, **Staging**, maupun **Server Produksi (VPS / Cloud PostgreSQL)**.

---

## 1. Prasyarat Lingkungan (Environment Prerequisites)

Sebelum menjalankan migrasi di server atau lokal, pastikan ekstensi dan konfigurasi PostgreSQL berikut telah aktif:

### A. Ekstensi UUID PostgreSQL
Muter-Muter App menggunakan **UUID v4** untuk seluruh Primary Key dan Foreign Key. Pastikan database PostgreSQL memiliki hak akses untuk ekstensi `pgcrypto` atau `uuid-ossp`.
```sql
-- Jalankan sekali di database PostgreSQL jika diperlukan
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
```

### B. Konfigurasi `.env`
Pastikan variabel lingkungan database mengarah ke PostgreSQL (bukan SQLite/MySQL):
```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=muter_muter
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password
```

---

## 2. Opsi A: Setup Database Baru / Fresh Installation (Disarankan)

Gunakan perintah ini saat pertama kali mendeploy aplikasi ke VPS atau ketika melakukan reset total database lokal/staging.

### Perintah Utama:
```bash
php artisan migrate:fresh --seed
```

### Apa yang Dilakukan oleh Perintah Ini?
1. Menghapus (drop) semua tabel lama di skema PostgreSQL.
2. Mengeksekusi seluruh migrasi dari tabel inti hingga indeks konkurensi (`database/migrations/`).
3. Menjalankan `DatabaseSeeder` yang secara otomatis memanggil seeder berurutan:
   - **`RoleAndUserSeeder`**:
     - Super Admin: `admin@laketoba.travel` / `password` (Role `ADMIN`)
     - Mitra Operator 1: `horas@tobadrive.id` / `password` (Role `PARTNER`, Owner "PT Toba Trans Mandiri")
     - Mitra Operator 2: `contact@samosirrental.com` / `password` (Role `PARTNER`, Owner "CV Samosir Tour & Rental")
     - Customer Sample: `budi@gmail.com` / `password` (Role `CUSTOMER`)
   - **`DestinationSeeder`**:
     - Menambahkan 5 klaster wisata Sumatera Utara: Parapat, Samosir Timur, Samosir Barat, Merek & Tongging, serta Medan Hub.
     - Menambahkan objek wisata unggulan (Bukit Holbung, Air Terjun Sipiso-piso, Huta Siallagan, Tomok, Air Terjun Efrata, Patung Sibea-bea, Waterfront Pangururan, Bukit Cinta, dll).
     - Menambahkan galeri foto destinasi ke tabel `destination_media`.
   - **`ProductSeeder`**:
     - Paket Tour Terintegrasi: One Day Tour, Multi Day Tour (termasuk paket famtrip resmi platform).
     - Armada Kendaraan: Toyota Innova Reborn, Avanza All New, HiAce Commuter, Bus Pariwisata dengan izin trayek sah.

### Jalankan Backfill Multibahasa (Opsional / Recommended):
Setelah seeding baseline, jalankan migrasi backfill terjemahan untuk memastikan seluruh katalog dan destinasi memiliki data bilingual (`id` dan `en`):
```bash
php artisan migrate --path=database/migrations/2026_11_09_200000_backfill_english_translations_for_seeded_catalog.php
```

---

## 3. Opsi B: Incremental Migration (Update pada Database yang Sudah Ada)

Jika database sudah memiliki data transaksi nyata (Production) dan Anda TIDAK BOLEH menghapus data pengguna atau pesanan, gunakan migrasi inkremental:

```bash
# 1. Pastikan aplikasi berada dalam mode maintenance (jika di produksi)
php artisan down --message="Sedang melakukan peningkatan sistem database..."

# 2. Jalankan migrasi yang belum terpasang
php artisan migrate --force

# 3. Bersihkan cache konfigurasi & skema
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Aktifkan kembali aplikasi
php artisan up
```

---

## 4. Daftar Lengkap Urutan File Migrasi

Urutan eksekusi file migrasi diatur secara kronologis berdasarkan dependensi relasi foreign key:

| No | File Migrasi | Deskripsi & Entitas |
|---|---|---|
| 01 | `0001_01_01_000000_create_users_table.php` | Tabel `users`, `password_reset_tokens`, `sessions` (UUID PK). |
| 02 | `0001_01_01_000001_create_cache_table.php` | Tabel `cache` dan `cache_locks`. |
| 03 | `0001_01_01_000002_create_jobs_table.php` | Tabel `jobs`, `job_batches`, `failed_jobs`. |
| 04 | `2026_11_06_140000_create_partner_tables.php` | Tabel `partners`, `partner_members`, `partner_documents`. |
| 05 | `2026_11_06_140001_create_destination_tables.php` | Tabel `destination_categories`, `destinations`, `destination_media`. |
| 06 | `2026_11_06_140002_create_product_tables.php` | Tabel `products`, `tour_packages`, `vehicles`, `product_media`, dll. |
| 07 | `2026_11_06_140003_create_booking_and_payment_tables.php` | Tabel `bookings`, `inventory_holds`, `payments`, `payment_proofs`, `refunds`. |
| 08 | `2026_11_06_140004_create_audit_log_tables.php` | Tabel `audit_logs` dan `booking_status_histories`. |
| 09 | `2026_11_07_120000_create_vendor_settlements_table.php` | Tabel `vendor_settlements` untuk payout mitra. |
| 10 | `2026_11_07_130000_add_reviews_and_ratings_tables.php` | Tabel `reviews` dan agregasi rating mitra/produk. |
| 11 | `2026_11_09_120000_add_gin_indexes_for_jsonb_translations.php` | Indeks PostgreSQL GIN pada kolom JSONB terjemahan nama & deskripsi. |
| 12 | `2026_11_09_200000_backfill_english_translations_for_seeded_catalog.php` | Backfill otomatis translation EN untuk seeder awal. |
| 13 | `2026_11_10_200000_add_destination_binding_and_driver_assignment.php` | Menambahkan FK `tour_itinerary_stops.destination_id`, `bookings.contact_unmasked`, `assigned_driver_name`, `assigned_vehicle_plate`. |
| 14 | `2026_11_10_210000_add_performance_and_concurrency_indexes.php` | Indeks komposit performa, anti race-condition, dan foreign keys query filtering. |

---

## 5. Troubleshooting & FAQ Eksekusi

### Q1: Error `SQLSTATE[08006] connection to server at 127.0.0.1 failed`
- **Penyebab:** Service PostgreSQL tidak sedang berjalan atau kredensial port 5432 salah.
- **Solusi:** 
  - Di Windows: Buka Services (`services.msc`) -> Pastikan service `postgresql-x64-XX` berstatus *Running*.
  - Di Linux/Ubuntu VPS: Jalankan `sudo systemctl status postgresql` dan `sudo systemctl start postgresql`.

### Q2: Error `type "uuid" does not exist` atau fungsi `gen_random_uuid()` tidak dikenal
- **Penyebab:** PostgreSQL belum mengaktifkan modul pgcrypto.
- **Solusi:** Jalankan query SQL: `CREATE EXTENSION IF NOT EXISTS "pgcrypto";` melalui `psql` atau PgAdmin.

### Q3: Menjalankan Perintah Seeder Tertentu Secara Terpisah
Jika Anda hanya ingin mengeksekusi ulang seeder destinasi atau katalog tanpa merusak data user:
```bash
# Seeder Destinasi saja
php artisan db:seed --class=DestinationSeeder

# Seeder Katalog Produk saja
php artisan db:seed --class=ProductSeeder
```

### Q4: Memverifikasi Keberhasilan Migrasi & Indeks
Untuk memeriksa status migrasi yang telah terpasang:
```bash
php artisan migrate:status
```
Untuk menguji performa indeks di database PostgreSQL:
```sql
EXPLAIN ANALYZE SELECT * FROM bookings WHERE booking_status = 'CONFIRMED' AND service_end_date <= CURRENT_DATE;
```
Hasil `Index Scan using bookings_booking_status_service_end_date_index` menandakan indeks bekerja secara optimal.

