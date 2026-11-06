# Open Questions & Resolution Log — Lake Toba Travel Marketplace

- **Document Version:** 1.0
- **Status:** Maintained per Grooming Iterations

---

## 1. Resolved Questions Log (Grooming 5 Nov 2026)

| ID | Topic | Question | Resolution / Decision | Status |
| :--- | :--- | :--- | :--- | :--- |
| **RES-01** | Booking SLA | Batas waktu respon vendor untuk ketersediaan? | **24 Jam** sejak booking berstatus `PENDING_APPROVAL`. | `RESOLVED` |
| **RES-02** | Timeout Action | Tindakan saat vendor tidak merespons dalam 24h? | **Manual Admin Intervention** (status `MANUAL_REVIEW`), bukan hard expire otomatis. | `RESOLVED` |
| **RES-03** | Approval Flow | Apakah admin harus approve setiap booking sebelum bayar DP? | Cukup **Vendor Approval** untuk memicu `AWAITING_PAYMENT`. Admin mengawasi & berhak intervensi. | `RESOLVED` |
| **RES-04** | Lead Time | Jarak minimum waktu pesan sebelum keberangkatan? | **H-1 (24 jam)** untuk Tour Packages; **H-12 jam** untuk Rental Kendaraan. | `RESOLVED` |
| **RES-05** | Inventory Hold | Berapa lama durasi hold inventaris setelah diapprove? | **24 Jam** untuk pembayaran DP. Lewat 24h belum bayar $\rightarrow$ status `EXPIRED` & inventory `RELEASED`. | `RESOLVED` |
| **RES-06** | DP Configuration | Skema persentase Down Payment? | Diatur fleksibel oleh Admin, dengan **default sistem 30%**. | `RESOLVED` |
| **RES-07** | Payment Flow | Alur pembayaran di MVP? | **Opsi A (Semi-Automated):** Form booking di web $\rightarrow$ Vendor approve $\rightarrow$ Muncul nominal DP & rekening platform di web + notif Go WA $\rightarrow$ Upload bukti transfer di web $\rightarrow$ Admin verifikasi jadi `CONFIRMED`. | `RESOLVED` |
| **RES-08** | Refund Scale | Persentase pengembalian dana saat customer batal? | **>7 hari:** 100%, **3–7 hari:** 50%, **<3 hari:** 0%. Dihitung dari nominal yang **sudah dibayarkan secara riil**. | `RESOLVED` |
| **RES-09** | Ferry Crossing | Pemodelan tiket penyeberangan kapal ferry? | **Bukan tiket lepas.** Termasuk sebagai bagian dari paket wisata (`TOUR` inclusion). | `RESOLVED` |
| **RES-10** | WhatsApp Service | Pilihan gateway pesan WhatsApp? | Menggunakan **Internal Go WhatsApp API Gateway** via modular `WhatsAppService` di Laravel. | `RESOLVED` |
| **RES-11** | Partner Roles | Akses akun staf mitra? | **2 Level:** `Partner Owner` (profil, bank payout, staff, produk) dan `Partner Staff` (approval ketersediaan & jadwal). | `RESOLVED` |
| **RES-12** | KYC Documents | Dokumen wajib registrasi mitra? | KTP Penanggung Jawab, Izin Usaha / NIB / SKU, dan Foto Unit Kendaraan / Armada. | `RESOLVED` |

---

## 2. Deferred & Future Scope Questions (Post-MVP)

The following items are deferred by design and must NOT be implemented in MVP Phase 1 without prior alignment:

### 2.1 Automated Payment Gateway (Phase 1.5 / 2.0)
- **Question:** Kapan payment gateway otomatis (Midtrans/Xendit) akan diaktifkan?
- **Decision Deferred:** Dilakukan setelah alur manual bank transfer berjalan stabil dan volume transaksi harian membutuhkan otomasi settlement virtual account / QRIS.
- **Handling:** Arsitektur kode harus mengisolasi payment logic di `app/Services/PaymentService` sehingga penambahan driver gateway baru tidak mengubah schema booking.

### 2.2 Go WhatsApp Gateway API Payload Specification
- **Question:** Struktur JSON payload dan autentikasi token spesifik dari Go WA server yang terpasang?
- **Decision Deferred:** Format spesifik akan disesuaikan saat konfigurasi lingkungan (`.env`).
- **Handling:** Dibuatkan interface `WhatsAppNotificationInterface` dengan mock fallback untuk lokal environment.

### 2.3 Official Ferry Live API Integration (Phase 2.0)
- **Question:** Apakah ada integrasi API langsung dengan ASDP Ferry Indonesia (Pelabuhan Ajibata / Ambarita)?
- **Decision Deferred:** Belum ada API publik resmi yang terbuka untuk umum; saat ini biaya ferry dimasukkan ke komponen biaya paket tour.

### 2.4 Multi-Product Single-Cart Checkout (Phase 2.0)
- **Question:** Apakah customer bisa memesan 1 paket tour dan 1 mobil rental sekaligus dalam 1 keranjang belanja?
- **Decision Deferred:** Pada MVP, 1 booking = 1 produk utama untuk menjaga kesederhanaan inventory locking dan tracking komisi.

