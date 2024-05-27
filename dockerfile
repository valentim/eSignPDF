FROM php:8.2-fpm-alpine

WORKDIR /var/www

RUN apk update && apk add --no-cache \
    bash \
    icu-dev \
    libpq \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    mbstring

COPY . .

COPY config/custom-php.ini /usr/local/etc/php/conf.d/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
