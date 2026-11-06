# Panduan Deployment & Launch Readiness — Muter-Muter Danau Toba

Dokumen ini memuat panduan komprehensif untuk deployment dan operasional produksi aplikasi **Muter-Muter Danau Toba Travel Marketplace** (Modular Monolith: Laravel 11, Inertia.js 2.x, Vue 3, Tailwind CSS, PostgreSQL).

---

## 1. Persyaratan Sistem Server (Production Prerequisites)

- **Operating System:** Linux (Ubuntu 22.04 LTS / 24.04 LTS disarankan)
- **Web Server:** Nginx (disarankan) atau Apache 2.4+
- **PHP Version:** PHP 8.2+ (ekstensi wajib: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`)
- **Database Engine:** PostgreSQL 15+ (ekstensi `pgcrypto` atau `uuid-ossp` untuk UUID v4)
- **Node.js & NPM:** Node.js v18.x LTS atau v20.x LTS & npm 10+
- **Composer:** Composer 2.x
- **Process / Service Manager:** Systemd / Supervisor untuk queue/scheduler

---

## 2. Langkah Instalasi & Konfigurasi Lingkungan

### 2.1 Clone Repositori & Install Dependencies
```bash
git clone https://github.com/organization/Muter-Muter-App.git /var/www/muter-muter
cd /var/www/muter-muter

# Install Composer PHP dependencies (Production optimized)
composer install --no-dev --optimize-autoloader

# Install NPM Node dependencies & Compile Assets
npm ci
npm run build
```

### 2.2 Konfigurasi Environment File (`.env`)
Salin template lingkungan:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan variabel penting terkonfigurasi dengan benar:
```ini
APP_NAME="Muter-Muter Danau Toba"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://muter-muter.id

# PostgreSQL Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=muter_muter_prod
DB_USERNAME=muter_user
DB_PASSWORD=your_secure_password

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

# Go WhatsApp API Gateway Integration
WHATSAPP_ENABLED=true
WHATSAPP_MOCK_MODE=false
WHATSAPP_GATEWAY_URL=http://127.0.0.1:3000/send-message
WHATSAPP_API_TOKEN=your_secure_bearer_token
WHATSAPP_TIMEOUT=5
```

---

## 3. Database Migrations, Seeding, dan Storage Link

```bash
# Buat symbolic link untuk upload file bukti transfer, KTP, dan foto armada
php artisan storage:link

# Jalankan migration skema PostgreSQL UUID v4
php artisan migrate --force

# Jalankan seeder baseline roles, permissions, admin awal, dan destinasi Danau Toba
php artisan db:seed --class=RoleAndUserSeeder --force
php artisan db:seed --class=DestinationSeeder --force
```

---

## 4. Konfigurasi Task Scheduler (Cron Job)

Sistem mengandalkan Laravel Scheduler untuk mengeksekusi 3 proses krusial otomasi marketplace:
1. **24-Hour Partner SLA Monitor:** `bookings:check-sla-timeouts` (setiap 15 menit) $\rightarrow$ Mengalihkan booking unresponded ke `MANUAL_REVIEW` untuk intervensi admin.
2. **24-Hour Inventory Hold Expiration:** `inventory:release-expired-holds` (setiap 15 menit) $\rightarrow$ Melepaskan unit yang di-hold jika DP tidak dibayar dalam 24 jam.
3. **Trip Completion & Settlement Unlocking:** `trips:mark-completed` (harian tengah malam) $\rightarrow$ Mengubah trip selesai ke `COMPLETED` dan membuka status pencairan dana vendor ke `ELIGIBLE`.

Tambahkan entri cron berikut pada crontab user web server (`crontab -e -u www-data`):
```bash
* * * * * cd /var/www/muter-muter && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Konfigurasi Nginx Web Server

Contoh konfigurasi Nginx vhost (`/etc/nginx/sites-available/muter-muter.conf`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name muter-muter.id www.muter-muter.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name muter-muter.id www.muter-muter.id;
    root /var/www/muter-muter/public;

    ssl_certificate /etc/letsencrypt/live/muter-muter.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/muter-muter.id/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    # Maximum file upload size (Payment Proofs, KTP, Fleet photos up to 10MB)
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 6. Optimasi Produksi (Production Cache)

Jalankan perintah optimasi sebelum membuka lalu lintas publik:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika terjadi perubahan konfigurasi di kemudian hari:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

---

## 7. Format Payload Integrasi Go WhatsApp Gateway

Service `App\Services\WhatsAppService` mengirim HTTP POST request dengan format JSON:

- **Endpoint:** `POST {WHATSAPP_GATEWAY_URL}`
- **Headers:**
  ```http
  Authorization: Bearer {WHATSAPP_API_TOKEN}
  Content-Type: application/json
  Accept: application/json
  ```
- **Payload:**
  ```json
  {
    "phone": "628123456789",
    "message": "Isi pesan notifikasi WhatsApp..."
  }
  ```
- **Expected Success Response:** HTTP 200/201 JSON dengan status success.

