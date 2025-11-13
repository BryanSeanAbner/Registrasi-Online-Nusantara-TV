#!/usr/bin/env sh
set -e

cd /www/registrasi-online

# Silence git ownership warning for bind-mounted working tree
git config --global --add safe.directory /www/registrasi-online 2>/dev/null || true

# Ensure default .env exists
if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

# Install PHP dependencies (guarded)
# COMPOSER_INSTALL_ON_START=always|auto|never (default: auto)
# - always: run every start
# - auto:   run only if vendor/autoload.php is missing (first boot)
# - never:  never run automatically
CIOS="${COMPOSER_INSTALL_ON_START:-auto}"
if [ "$CIOS" = "always" ] || { [ "$CIOS" = "auto" ] && [ ! -f vendor/autoload.php ]; }; then
  echo "Running composer install (mode=$CIOS) ..."
  composer install --prefer-dist --no-progress --no-interaction || true
else
  echo "Skipping composer install (COMPOSER_INSTALL_ON_START=$CIOS)"
fi

# Permissions for storage and cache
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# Generate app key if missing
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --ansi || true
fi

# Run migrations only when explicitly enabled
# MIGRATE_ON_START=true to enable
if [ "${MIGRATE_ON_START:-false}" = "true" ]; then
  if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    DB_HOST_SAFE="${DB_HOST:-mysql}"
    DB_PORT_SAFE="${DB_PORT:-3306}"
    echo "Waiting for MySQL at ${DB_HOST_SAFE}:${DB_PORT_SAFE}..."
    # Wait for TCP socket
    until php -r 'exit(@fsockopen(getenv("DB_HOST")?:"'"${DB_HOST_SAFE}"'", (int)(getenv("DB_PORT")?:'"${DB_PORT_SAFE}"') )?0:1);'; do
      sleep 2
    done
  fi
  echo "Running database migrations (MIGRATE_ON_START=true) ..."
  php artisan migrate --force || true
else
  echo "Skipping migrations (MIGRATE_ON_START=${MIGRATE_ON_START:-false})"
fi

# Optionally run a background queue worker in the app container
# Enable with QUEUE_WORKER_IN_APP=true
if [ "${QUEUE_WORKER_IN_APP:-false}" = "true" ]; then
  echo "Starting background queue worker in app container..."
  : "${QUEUE_WORKER_ARGS:=--tries=1 --timeout=60}"
  nohup php artisan queue:work $QUEUE_WORKER_ARGS > storage/logs/queue-worker.log 2>&1 &
else
  echo "Skipping queue worker in app (QUEUE_WORKER_IN_APP=${QUEUE_WORKER_IN_APP:-false})"
fi

# Ensure storage symlink
php artisan storage:link >/dev/null 2>&1 || true

exec "$@"
