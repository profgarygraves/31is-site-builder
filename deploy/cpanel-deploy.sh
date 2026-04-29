#!/usr/bin/env bash
#
# cPanel "Pull or Deploy" hook — runs from the repo root via .cpanel.yml.
# Idempotent; safe to re-run on every deploy.
#
# Requires: web/.env created manually (via cPanel File Manager) before
# the first deploy. See deploy/env.production.example for the template.

set -euo pipefail

cd "$(dirname "$0")/.."     # repo root
echo "==> Deploy from $(pwd)"
cd web

# 1. Find a usable PHP. cPanel ships multiple via EA-PHP; prefer modern.
PHP=""
for v in 84 83 82; do
  candidate="/opt/cpanel/ea-php${v}/root/usr/bin/php"
  if [ -x "$candidate" ]; then
    PHP="$candidate"
    break
  fi
done
PHP="${PHP:-$(command -v php || true)}"
if [ -z "$PHP" ]; then
  echo "ERROR: no PHP binary found. Set PHP version in cPanel MultiPHP Manager." >&2
  exit 1
fi
echo "==> PHP: $PHP ($($PHP -r 'echo PHP_VERSION;'))"

# 2. Composer — install locally if not present.
if [ ! -f composer.phar ]; then
  echo "==> Bootstrapping composer.phar"
  curl -sS https://getcomposer.org/installer | "$PHP"
fi
echo "==> composer install --no-dev --optimize-autoloader"
"$PHP" composer.phar install --no-dev --optimize-autoloader --no-interaction

# 3. .env sanity check.
if [ ! -f .env ]; then
  echo
  echo "ERROR: web/.env not found." >&2
  echo "       Use cPanel File Manager to create web/.env from" >&2
  echo "       deploy/env.production.example, fill in DB+SMTP, then redeploy." >&2
  exit 1
fi

# 4. APP_KEY (only generate if missing).
if grep -qE '^APP_KEY=\s*$' .env || ! grep -qE '^APP_KEY=' .env; then
  echo "==> Generating APP_KEY"
  "$PHP" artisan key:generate --force
fi

# 5. Migrate.
echo "==> Running migrations"
"$PHP" artisan migrate --force --seed

# 6. Production caches.
echo "==> Caching config / routes / views"
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

# 7. Permissions for storage + bootstrap/cache.
chmod -R u+rwX,g+rwX storage bootstrap/cache 2>/dev/null || true

echo
echo "==> Deploy complete."
"$PHP" artisan about | head -30 || true
