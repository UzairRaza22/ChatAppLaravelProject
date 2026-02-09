# Use PHP 8.5 FPM as base image
FROM php:8.5-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    jpegoptim optipng pngquant gifsicle \
    supervisor \
    netcat-openbsd \
    libssl-dev \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip

# Install MongoDB PHP extension with SSL support
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Change ownership of the storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy PHP configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Copy supervisor configuration
COPY docker/php/supervisor.conf /etc/supervisor/conf.d/laravel-worker.conf

# Copy startup script
COPY docker/php/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Create log directory for PHP
RUN mkdir -p /var/log/php

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Use startup script as entrypoint that starts PHP-FPM
ENTRYPOINT ["/usr/local/bin/start.sh"]
