# syntax=docker/dockerfile:1

FROM php:8.3-fpm-alpine

LABEL org.opencontainers.image.source="uex-contacts-monolith"

# Install system deps
RUN apk add --no-cache bash git curl libzip-dev oniguruma-dev icu-dev icu-data-full libpng-dev libjpeg-turbo-dev freetype-dev postgresql-dev

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd intl bcmath pcntl zip pdo pdo_pgsql

# Install Composer
ENV COMPOSER_HOME=/composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Helpful PHP config
COPY conf/php.ini /usr/local/etc/php/conf.d/app.ini

CMD ["php-fpm"]

