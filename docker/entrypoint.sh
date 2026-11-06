#!/bin/sh
set -e

# Ensure writable storage directories
mkdir -p /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views /app/storage/app/public /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Enforce PHP memory limit to 256M to fit VPS budget
if [ -d "/usr/local/etc/php/conf.d" ]; then
    echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/docker-php-memlimit.ini
fi

# Production cache optimizations
if [ -n "$APP_KEY" ]; then
    echo "[Entrypoint] Optimizing Laravel configuration and caches..."
    php artisan storage:link --force || true
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec "$@"

