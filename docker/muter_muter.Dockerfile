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
    for i in 1 2 3; do \
        composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs && break || sleep 10; \
    done
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
RUN npm run build

# ==========================================
# STAGE 3: PRODUCTION RUNNER (FrankenPHP)
# ==========================================
FROM dunglas/frankenphp:latest-php8.2-alpine AS runner

# Install PostgreSQL and essential PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    gd \
    exif \
    bcmath \
    intl \
    opcache \
    zip \
    pcntl

WORKDIR /app

# Set production environment defaults
ENV APP_ENV=production
ENV SERVER_NAME=":8000"
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# Copy source code and pre-built artifacts
COPY . /app
COPY --from=vendor-builder /app/vendor /app/vendor
COPY --from=frontend-builder /app/public/build /app/public/build

# Copy Caddyfile and Entrypoint
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose internal HTTP port
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

