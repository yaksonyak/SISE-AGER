FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        acl \
        bash \
        git \
        icu-dev \
        libpq-dev \
        linux-headers \
        oniguruma-dev \
        unzip \
    && docker-php-ext-install \
        intl \
        opcache \
        pdo \
        pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json symfony.lock ./
RUN composer install --prefer-dist --no-interaction --no-progress --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p var/cache var/log config/jwt \
    && chown -R www-data:www-data var config/jwt

USER www-data

CMD ["php-fpm"]
