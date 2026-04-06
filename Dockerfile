# syntax=docker/dockerfile:1

FROM php:8.2-fpm

WORKDIR /var/www/html

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    DB_CONNECTION=mysql \
    DB_HOST=host.docker.internal \
    DB_PORT=3306 \
    DB_DATABASE=keuangan-l12 \
    DB_USERNAME=root \
    DB_PASSWORD= \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    FILESYSTEM_DISK=public \
    LOG_CHANNEL=stack \
    MAIL_MAILER=log

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    nginx \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring bcmath intl gd zip \
    && rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf \
    && mkdir -p /run/php /var/lib/nginx/body /var/log/nginx \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint
COPY docker/start.sh /usr/local/bin/app-start
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

RUN chmod +x /usr/local/bin/app-entrypoint \
    && chmod +x /usr/local/bin/app-start \
    && mkdir -p storage/app/public bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["app-entrypoint"]
CMD ["app-start"]
