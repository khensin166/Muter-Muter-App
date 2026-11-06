#!/bin/sh
set -e

# Ensure writable storage directories
mkdir -p /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views /app/storage/app/public /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Production cache optimizations
if [ -n "$APP_KEY" ]; then
    echo "[Entrypoint] Optimizing Laravel configuration and caches..."
    php artisan storage:link --force || true
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec "$@"

