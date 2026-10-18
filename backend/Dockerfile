# ==============================================================================
# Dockerfile: Laravel RESTful API Backend with FrankenPHP (Production Ready)
# Bonus CPMK4 & CPMK5 (+10 Nilai Tambahan FrankenPHP Server)
# ==============================================================================
FROM dunglas/frankenphp:1-php8.4-alpine AS runner

# Install required PHP extensions for PostgreSQL and Laravel
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    pcntl

# Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Set server port and FrankenPHP config
ENV SERVER_NAME=":8000"
ENV FRANKENPHP_CONFIG="root ./public"

# 1. Composer layer caching: Copy dependency manifests first
COPY composer.json composer.lock* ./

# 2. Install dependencies without generating autoload and dev packages
RUN composer install --no-dev --no-autoloader --no-scripts --prefer-dist

# 3. Copy application codebase (excluding items in .dockerignore)
COPY . .

# 4. Generate optimized classloader
RUN composer dump-autoload --optimize --no-dev

# Set secure ownership and permissions for Laravel writable directories
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Copy custom Caddyfile for explicit :8000 routing
COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 8000

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]