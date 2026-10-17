#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/kosku}"
GIT_BRANCH="${GIT_BRANCH:-main}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.5-fpm}"

echo "==> Deploy ke ${APP_DIR} (branch ${GIT_BRANCH})"
cd "${APP_DIR}"

echo "==> git pull"
git fetch --prune origin
git checkout "${GIT_BRANCH}"
git pull --ff-only origin "${GIT_BRANCH}"

echo "==> composer install"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> build frontend"
npm ci
npm run build

echo "==> migrate"
php artisan migrate --force

echo "==> cache"
php artisan config:cache
php artisan route:cache

echo "==> reload php-fpm"
sudo systemctl reload "${PHP_FPM_SERVICE}"

echo "==> Deploy selesai"
