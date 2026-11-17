#!/bin/sh
# Boots the public demo: fresh SQLite database with deterministic data,
# cached config/routes/views, then FrankenPHP on Render's $PORT.
set -e

cd /app

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi

# Render's generateValue gives raw base64 (32 bytes); Laravel wants a prefix.
case "$APP_KEY" in
    base64:*) ;;
    *) export APP_KEY="base64:$APP_KEY" ;;
esac

# Render exposes the public URL; use it unless APP_URL was set explicitly.
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:${PORT:-8080}}}"

# Render terminates TLS, so Caddy serves plain HTTP on the given port.
export SERVER_NAME=":${PORT:-8080}"

touch database/database.sqlite

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan demo:seed --force

exec frankenphp run --config /etc/frankenphp/Caddyfile
