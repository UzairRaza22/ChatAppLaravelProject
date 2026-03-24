FROM ubuntu:22.04



# Prevent interactive prompts during install

ENV DEBIAN_FRONTEND=noninteractive



# Install system dependencies + Nginx + Redis

RUN apt-get update && apt-get install -y \

    nginx \

    redis-server \

    curl \

    git \

    unzip \

    nano \

    procps \

    software-properties-common \

    && rm -rf /var/lib/apt/lists/*



# Add PHP 8.2 repository

RUN add-apt-repository ppa:ondrej/php -y && apt-get update

<<<<<<< HEAD


# Install PHP 8.2 with required extensions (excluding mongodb)

=======
# Install PHP 8.2 with required extensions (excluding mongodb)
>>>>>>> 17daed8c084fd2b92852bcedc61e0e99c02926f4
RUN apt-get install -y \

    php8.2-fpm \

    php8.2-cli \
<<<<<<< HEAD

=======
>>>>>>> 17daed8c084fd2b92852bcedc61e0e99c02926f4
    php8.2-xml \

    php8.2-mbstring \

    php8.2-zip \

    php8.2-curl \

    php8.2-redis \
<<<<<<< HEAD

    php-pear \

    php8.2-dev \

    && rm -rf /var/lib/apt/lists/*



# Install MongoDB extension via PECL

RUN pecl install mongodb \

    && echo "extension=mongodb.so" > /etc/php/8.2/mods-available/mongodb.ini \

    && phpenmod mongodb



=======
    php-pear \
    php8.2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install MongoDB extension via PECL
RUN pecl install mongodb \
    && echo "extension=mongodb.so" > /etc/php/8.2/mods-available/mongodb.ini \
    && phpenmod mongodb

>>>>>>> 17daed8c084fd2b92852bcedc61e0e99c02926f4
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

COPY php-uploads.ini /etc/php/8.2/fpm/conf.d/99-uploads.ini

COPY php-uploads.ini /etc/php/8.2/cli/conf.d/99-uploads.ini



# Copy PHP-FPM custom config

COPY php-fpm-custom.conf /etc/php/8.2/fpm/pool.d/custom.conf



# Copy all project files

COPY . /var/www/backend/



# Set correct permissions for Laravel

RUN chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache \

    && chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache



EXPOSE 80



CMD /etc/init.d/php8.2-fpm start && /etc/init.d/redis-server start && nginx -g "daemon off;"
