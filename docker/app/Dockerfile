FROM ghcr.io/roadrunner-server/roadrunner:2025 AS rr

FROM php:8.4-cli-alpine

RUN apk add --no-cache \
        bash \
        git \
        unzip \
        icu-dev \
        libzip-dev \
        linux-headers \
    && docker-php-ext-install \
        intl \
        sockets \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=rr /usr/bin/rr /usr/local/bin/rr

COPY docker/app/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www
