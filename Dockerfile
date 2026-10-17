# Stage 1: Vendor (composer)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

# Stage 2: Aset frontend (node)
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# Stage 3: Runtime (PHP-FPM + Nginx di satu image)
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        nginx \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        zip

WORKDIR /var/www

COPY . .

COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /usr/bin/composer /usr/local/bin/composer
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx-default.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint \
    && mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && composer dump-autoload --no-dev --optimize

EXPOSE 9000 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
CMD ["php-fpm"]