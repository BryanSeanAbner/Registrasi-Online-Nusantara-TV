#!/usr/bin/env sh
set -e

cd /www/registrasi-online

# Ensure default .env exists
if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

# Install PHP dependencies (idempotent)
composer install --prefer-dist --no-progress --no-interaction || true

# Permissions for storage and cache
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# Generate app key if missing
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --ansi || true
fi

# If using MySQL, wait for DB and run migrations
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
  DB_HOST_SAFE="${DB_HOST:-mysql}"
  DB_PORT_SAFE="${DB_PORT:-3306}"
  echo "Waiting for MySQL at ${DB_HOST_SAFE}:${DB_PORT_SAFE}..."
  # Wait for TCP socket
  until php -r 'exit(@fsockopen(getenv("DB_HOST")?:"'"${DB_HOST_SAFE}"'", (int)(getenv("DB_PORT")?:'"${DB_PORT_SAFE}"') )?0:1);'; do
    sleep 2
  done
  echo "MySQL is up. Running migrations..."
  php artisan migrate --force || true
  php artisan queue:work --tries=1 --timeout=60 || true
fi

# Ensure storage symlink
php artisan storage:link >/dev/null 2>&1 || true

exec "$@"
