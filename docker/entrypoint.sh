#!/bin/sh
set -e

mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ ! -L public/storage ]; then
    ln -s /var/www/storage/app/public public/storage
fi

exec "$@"