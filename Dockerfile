# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Runtime base: FrankenPHP (Caddy + PHP 8.4) serving /app/public.
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4 AS base

RUN install-php-extensions intl zip opcache pcntl \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

WORKDIR /app

# ---------------------------------------------------------------------------
# Build: Composer + Node. Wayfinder generates the TypeScript route helpers by
# running artisan during `vite build`, so this stage needs both PHP and Node.
# ---------------------------------------------------------------------------
FROM base AS build

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && cp .env.example .env \
    && php artisan key:generate --force \
    && npm run build \
    && rm -rf node_modules .env tests

# ---------------------------------------------------------------------------
# Production image.
# ---------------------------------------------------------------------------
FROM base

ENV APP_NAME="Plug N Paw Admin" \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    MAIL_MAILER=log \
    DEMO_MODE=true

COPY --from=build /app /app
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["entrypoint"]
