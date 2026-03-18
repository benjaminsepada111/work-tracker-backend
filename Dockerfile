FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    storage/logs bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

RUN rm -f bootstrap/cache/routes-v7.php \
    bootstrap/cache/config.php \
    bootstrap/cache/events.php

EXPOSE 8080

ENTRYPOINT ["/bin/sh", "-c", "php artisan route:clear && php artisan cache:clear && php artisan migrate --force && php -S 0.0.0.0:${PORT:-8080} -t public"]
