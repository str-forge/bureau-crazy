FROM php:8.3-fpm-alpine

RUN apk add --no-cache git icu-dev libpq-dev zip unzip
RUN docker-php-ext-install intl pdo pdo_pgsql

# Composer direkt aus dem offiziellen Image ziehen
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
