FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring zip bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

# Set permissions
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    storage/logs bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

# DO NOT cache routes during build
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan cache:clear || true

# Expose port
EXPOSE 8080

# Start command
CMD php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
