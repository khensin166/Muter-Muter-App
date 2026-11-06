# Panduan Konfigurasi CI/CD GitHub Actions & VPS Deployment

Dokumen ini menjelaskan alur kerja otomatisasi **CI/CD pipeline** untuk **Muter-Muter Danau Toba Travel Marketplace** yang diadaptasi dari arsitektur Kainest.

Pipeline ini otomatis berjalan setiap ada `git push` ke branch `main` atau `master`.

---

## 1. Arsitektur Pipeline CI/CD

Workflow file: [`.github/workflows/muter-muter-cicd.yml`](file:///d:/eksplor/Muter-Muter-App/.github/workflows/muter-muter-cicd.yml)

```
[ Git Push to main/master ]
           │
           ▼
┌────────────────────────────────────────┐
│  JOB 0: pest_test                      │
│  - Setup PHP 8.2 & Node.js 20          │
│  - Composer install & npm build        │
│  - Jalankan 84 Pest automated tests    │
└────────────────────────────────────────┘
           │ (Hanya lanjut jika 100% lolos)
           ▼
┌────────────────────────────────────────┐
│  JOB 1: build_image                    │
│  - Build Multi-Stage Dockerfile        │
│  - Push ke GitHub Container Registry   │
│    (ghcr.io/<repo>:latest & :<sha>)    │
└────────────────────────────────────────┘
           │
           ▼
┌────────────────────────────────────────┐
│  JOB 2: deploy_to_server (Ansible)     │
│  - SSH ke VPS target                   │
│  - Generate .env & docker-compose      │
│  - Pull & recreate container           │
│  - Jalankan php artisan migrate        │
│  - Jalankan php artisan optimize       │
│  - Healthcheck container               │
└────────────────────────────────────────┘
```

---

## 2. Daftar Nilai yang Perlu Diinput di GitHub Repository

Buka repositori GitHub Anda:  
**Settings** $\rightarrow$ **Secrets and variables** $\rightarrow$ **Actions**

### A. Repository Secrets (Informasi Rahasia / Sensitif)
Tambahkan pada tab **Secrets** $\rightarrow$ **New repository secret**:

| Nama Secret | Contoh Nilai | Keterangan |
| :--- | :--- | :--- |
| `VPS_SSH_KEY` | `-----BEGIN OPENSSH PRIVATE KEY----- ... -----END OPENSSH PRIVATE KEY-----` | Private key SSH untuk login ke VPS target tanpa password |
| `APP_KEY` | `base64:SWe6CTqDWO8vbTyGXNqC2LwCoZ+T+qWuAXs6WMnxf1Q=` | Kunci enkripsi Laravel (hasil `php artisan key:generate`) |
| `DB_PASSWORD` | `your_secure_postgres_password` | Password database PostgreSQL di server/VPS |
| `WHATSAPP_API_TOKEN` | `bearer_token_gowa_kamu` | Token autentikasi Go WhatsApp Gateway Anda |

---

### B. Repository Variables (Konfigurasi Umum / Non-Sensitif)
Tambahkan pada tab **Variables** $\rightarrow$ **New repository variable**:

| Nama Variable | Contoh Nilai | Status | Keterangan |
| :--- | :--- | :--- | :--- |
| `VPS_IP` | `103.127.xxx.xxx` | **Wajib** | Alamat IP Publik server VPS Anda |
| `VPS_USER` | `root` (atau `ubuntu`) | **Wajib** | Username SSH di server VPS |
| `APP_URL` | `https://muter-muter.id` | **Wajib** | Domain publik aplikasi produksi |
| `APP_PORT` | `8282` | Opsional (Default: 8282) | Port host yang diekspos oleh container |
| `DB_HOST` | `muter_postgres` | **Opsional** (Default: `muter_postgres`) | Otomatis terhubung ke container PostgreSQL bawaan |
| `DB_PORT` | `5432` | Opsional (Default: 5432) | Port database PostgreSQL |
| `DB_DATABASE` | `muter_muter_prod` | Opsional (Default: `muter_muter_prod`) | Nama database PostgreSQL di server |
| `DB_USERNAME` | `postgres` | Opsional (Default: `postgres`) | User database PostgreSQL |
| `WHATSAPP_ENABLED` | `true` | Opsional (Default: `true`) | Mengaktifkan modul WhatsApp Service |
| `WHATSAPP_MOCK_MODE` | `false` | Opsional (Default: `false`) | `false` untuk mengirim WA nyata ke nomor HP |
| `WHATSAPP_GATEWAY_URL` | `http://103.127.xxx.xxx:3000/send-message` | **Wajib** | URL endpoint API Go WA Gateway Anda |
| `WHATSAPP_TIMEOUT` | `5` | Opsional (Default: 5) | Batas timeout request HTTP ke gateway (detik) |

---

## 3. Database PostgreSQL Bawaan (Sudah Termasuk)

File [`docker-compose.deploy.yml`](file:///d:/eksplor/Muter-Muter-App/docker-compose.deploy.yml) **sudah menyertakan service database PostgreSQL 15**:

1. **Otomatis Dijalankan di VPS:**
   Saat Ansible menjalankan `docker compose up -d`, service `muter_postgres` (image `postgres:15-alpine`) akan otomatis diunduh dan dijalankan bersamaan dengan aplikasi web.
2. **Data Persisten (Aman saat Update):**
   Data PostgreSQL disimpan di Docker volume bernama `muter_db_data`. Ketika ada pembaruan kode atau rebuild image, data database **tetap utuh dan tidak akan hilang**.
3. **Healthcheck Otomatis:**
   Container aplikasi web `muter_muter_app` dikonfigurasi dengan `depends_on: muter_postgres: condition: service_healthy`. Sistem akan menunggu database benar-benar siap menerima koneksi sebelum menjalankan `php artisan migrate --force`.
4. **Opsi Database Eksternal:**
   Jika di masa depan Anda ingin menggunakan database terpisah di luar Docker (misal AWS RDS, DigitalOcean Managed Database, atau PostgreSQL bawaan host VPS), Anda cukup mengisi variable `DB_HOST` dengan IP/domain database eksternal tersebut.

---

## 4. Pembatasan Resource Server (RAM & CPU) & Path Lokasi

### Lokasi Direktori di VPS:
File konfigurasi dan project dideploy ke direktori:
`/opt/muter-muter-app` (berisi `docker-compose.yml` dan `.env`).

### Alokasi Resource (Budget RAM < 1 GB):
Untuk menjaga sisa RAM VPS (2 GB) tetap aman dan tidak terkena *Out of Memory (OOM)*, kedua container telah dibatasi ketat:
- **`muter_muter_app` (Web):** Maksimal **640 MB** RAM (`mem_limit: 640m`, limit PHP: `256M`, CPU: 1.0 core).
- **`muter_postgres` (Database):** Maksimal **384 MB** RAM (`mem_limit: 384m`, CPU: 0.5 core).
- **Total Beban Maksimum:** ~1 GB RAM, menyisakan > 1 GB RAM untuk OS Ubuntu dan container lain yang sudah berjalan (`porto-analysis-api`, `kainest-backend`, dll).

---

## 5. Persiapan di Sisi Server VPS (One-time Setup)

Sebelum trigger pipeline pertama kali, pastikan hal berikut di VPS Anda:

1. **Docker & Docker Compose Terpasang:**
   ```bash
   docker --version
   docker compose version
   ```

2. **Jaringan Docker `muter_net`:**
   Playbook Ansible otomatis membuat jaringan ini jika belum ada. Namun jika ingin dibuat manual terlebih dahulu:
   ```bash
   docker network create muter_net
   ```

3. **Izin Akses SSH Public Key:**
   Pastikan public key dari `VPS_SSH_KEY` sudah terdaftar di `~/.ssh/authorized_keys` di VPS Anda.

---

## 6. File-File CI/CD yang Dihasilkan

1. [`.github/workflows/muter-muter-cicd.yml`](file:///d:/eksplor/Muter-Muter-App/.github/workflows/muter-muter-cicd.yml) — GitHub Actions pipeline definition.
2. [`docker/muter_muter.Dockerfile`](file:///d:/eksplor/Muter-Muter-App/docker/muter_muter.Dockerfile) — Multi-stage production container build (Vite + Composer + FrankenPHP).
3. [`docker/Caddyfile`](file:///d:/eksplor/Muter-Muter-App/docker/Caddyfile) — Caddy/FrankenPHP HTTP & asset routing.
4. [`docker/entrypoint.sh`](file:///d:/eksplor/Muter-Muter-App/docker/entrypoint.sh) — Startup script (storage link & cache optimization).
5. [`docker-compose.deploy.yml`](file:///d:/eksplor/Muter-Muter-App/docker-compose.deploy.yml) — Docker Compose production orchestration.
6. [`deploy/ansible/deploy.yml`](file:///d:/eksplor/Muter-Muter-App/deploy/ansible/deploy.yml) — Ansible automated deployment playbook.
7. [`.dockerignore`](file:///d:/eksplor/Muter-Muter-App/.dockerignore) — Exclude local artifacts from build context.

