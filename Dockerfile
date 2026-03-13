FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    git \
    unzip \
    nano \
    libssl-dev \
    pkg-config \
    libzip-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions needed for Laravel
RUN docker-php-ext-install pcntl bcmath zip gd

# Install MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/backend

# Copy nginx config
COPY nginx-default.conf /etc/nginx/sites-available/default

# Ensure sites-enabled symlink exists
RUN mkdir -p /etc/nginx/sites-enabled && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copy PHP ini config
COPY php-uploads.ini /usr/local/etc/php/conf.d/99-uploads.ini

# Copy all project files
COPY . /var/www/backend/

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache \
    && chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache

# Copy and set permissions for startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]