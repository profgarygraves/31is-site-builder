#!/usr/bin/env bash
#
# One-shot deploy script for the cPanel install.
#
# Workflow:
#   1. Pull latest main into ~/31is-fresh (clones on first run)
#   2. rsync the web/ folder into the live install at ~/public_html/31is.com/web/
#      (preserves .env, vendor/, storage/, etc.)
#   3. composer install --no-dev (only fetches if composer.lock changed)
#   4. php artisan migrate / cache rebuilds
#
# Idempotent — safe to re-run after every push to GitHub.

set -euo pipefail

REPO_URL="https://github.com/profgarygraves/31is-site-builder.git"
FRESH="$HOME/31is-fresh"
LIVE="$HOME/public_html/31is.com/web"
PHP="${PHP:-/opt/cpanel/ea-php84/root/usr/bin/php}"

echo "==> Pulling latest from GitHub"
if [ ! -d "$FRESH/.git" ]; then
    git clone --depth 1 "$REPO_URL" "$FRESH"
else
    git -C "$FRESH" fetch --depth 1 origin main
    git -C "$FRESH" reset --hard origin/main
fi

echo "==> Syncing web/ → live install"
# Preserve everything that lives in production but isn't in source control.
rsync -a --delete \
    --exclude='.env' \
    --exclude='/vendor/' \
    --exclude='/node_modules/' \
    --exclude='/storage/framework/cache/' \
    --exclude='/storage/framework/sessions/' \
    --exclude='/storage/framework/views/' \
    --exclude='/storage/logs/' \
    --exclude='/storage/app/' \
    --exclude='/public/build/' \
    "$FRESH/web/" "$LIVE/"

echo "==> composer install (production)"
cd "$LIVE"
"$PHP" /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || \
    "$PHP" composer.phar install --no-dev --optimize-autoloader --no-interaction

echo "==> Migrations (force, no seed — seed only on first install)"
"$PHP" artisan migrate --force

echo "==> Ensure public storage symlink (idempotent)"
"$PHP" artisan storage:link 2>&1 | grep -v "already exists" || true

echo "==> Rebuild caches"
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

echo "==> Permissions"
chmod -R u+rwX,g+rwX storage bootstrap/cache 2>/dev/null || true

echo
echo "==> Deploy complete."
"$PHP" artisan about | head -15
