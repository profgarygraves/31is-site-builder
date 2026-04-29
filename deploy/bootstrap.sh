#!/usr/bin/env bash
#
# Bootstrap a fresh deploy of 31is.com on a cPanel server.
# Run from the repo root: bash deploy/bootstrap.sh
#
# Idempotent — safe to re-run (e.g. after a `git pull` to redeploy).

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WEB="$ROOT/web"
ENV_FILE="$WEB/.env"
ENV_TEMPLATE="$ROOT/deploy/env.production.example"

cd "$ROOT"
echo "==> Repo root: $ROOT"

# 1. PHP version sanity check
if ! command -v php >/dev/null; then
  echo "ERROR: php not on PATH. On GoDaddy cPanel try /opt/cpanel/ea-php82/root/usr/bin/php or use 'cpanel-php-cli'." >&2
  exit 1
fi
PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
echo "==> PHP $PHP_VER detected"

# 2. Composer dependencies
echo "==> Installing PHP dependencies (no-dev, optimized)"
cd "$WEB"
if ! command -v composer >/dev/null; then
  echo "==> composer not on PATH; downloading composer.phar locally"
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet
  rm composer-setup.php
  COMPOSER="php $WEB/composer.phar"
else
  COMPOSER="composer"
fi
$COMPOSER install --no-dev --optimize-autoloader --no-interaction

# 3. .env
if [ ! -f "$ENV_FILE" ]; then
  echo "==> Creating $ENV_FILE from template"
  cp "$ENV_TEMPLATE" "$ENV_FILE"
  echo
  echo "Edit $ENV_FILE with your DB, SMTP, and APP_URL settings before continuing."
  echo "Then re-run: bash deploy/bootstrap.sh"
  exit 0
fi

# 4. APP_KEY
if grep -q '^APP_KEY=$' "$ENV_FILE" || ! grep -q '^APP_KEY=' "$ENV_FILE"; then
  echo "==> Generating APP_KEY"
  php artisan key:generate --force
fi

# 5. Migrations + seed
echo "==> Running migrations"
php artisan migrate --force --seed

# 6. Caches (production performance)
echo "==> Caching config / routes / views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Storage permissions (cPanel often runs PHP as user, so 755/775 is enough)
echo "==> Fixing permissions"
chmod -R u+rwX "$WEB/storage" "$WEB/bootstrap/cache"

# 8. Health check
echo
echo "==> Done. Quick checks:"
php artisan about | grep -E "Environment|Debug|URL|Driver" || true

echo
echo "Next steps (do these in cPanel UI):"
echo "  1. Repoint app.31is.com document root to: $WEB/public"
echo "  2. Repoint *.31is.com  document root to: $WEB/public"
echo "  3. Repoint 31is.com    document root to: $WEB/public  (or symlink public_html)"
echo
echo "Then visit http://app.31is.com/login"
