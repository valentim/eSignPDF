FROM php:8.2-fpm-alpine

WORKDIR /var/www

RUN apk update && apk add --no-cache \
    bash \
    icu-dev \
    libpq \
    libzip-dev \
    oniguruma-dev \
    nginx \
    nodejs \
    npm \
    curl \
    git \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    mbstring \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

COPY .platform/custom-php.ini /usr/local/etc/php/conf.d/

RUN composer install --no-dev --optimize-autoloader

RUN npm install
RUN npm run build

RUN chown -R www-data:www-data /var/www
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

COPY .platform/nginx/nginx.conf /etc/nginx
COPY .platform/nginx/default.conf /etc/nginx/conf.d/

COPY .platform/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
