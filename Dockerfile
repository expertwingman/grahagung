# ---------- Tahap 1: build aset frontend ----------
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ---------- Tahap 2: aplikasi PHP ----------
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl zip unzip nginx \
      libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zlib1g-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_pgsql pcntl gd bcmath zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
      --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Ambil aset yang sudah di-build dari tahap 1
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
