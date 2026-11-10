# ==========================================
# STAGE 1: COMPOSER VENDOR (PHP Dependencies)
# ==========================================
FROM composer:2 AS vendor-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=secret,id=github_token \
    if [ -f /run/secrets/github_token ]; then \
        composer config --global github-oauth.github.com "$(cat /run/secrets/github_token)"; \
    fi && \
    composer config --global process-timeout 600 && \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev

# ==========================================
# STAGE 2: FRONTEND BUILD (Vite + Vue 3)
# ==========================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=vendor-builder /app/vendor ./vendor
ARG VITE_GOOGLE_MAPS_API_KEY
ENV VITE_GOOGLE_MAPS_API_KEY=$VITE_GOOGLE_MAPS_API_KEY
RUN npm run build

# ==========================================
# STAGE 3: PRODUCTION RUNNER (FrankenPHP)
# ==========================================
FROM dunglas/frankenphp:latest-php8.2-alpine AS runner

# Install essential PHP extensions & clean package cache immediately
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    gd \
    exif \
    bcmath \
    intl \
    opcache \
    zip \
    pcntl \
    && rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

WORKDIR /app

# Set production environment defaults
ENV APP_ENV=production
ENV SERVER_NAME=":8000"
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# Pre-create writable storage structure with proper ownership
RUN mkdir -p \
    /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/app/public \
    /app/storage/logs \
    /app/bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Copy clean application source code (filtered strictly by .dockerignore)
COPY --chown=www-data:www-data . /app
COPY --chown=www-data:www-data --from=vendor-builder /app/vendor /app/vendor
COPY --chown=www-data:www-data --from=frontend-builder /app/public/build /app/public/build

# Copy Caddyfile and Entrypoint
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose internal HTTP port
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
