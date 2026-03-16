#!/bin/bash

# Remove broken symlink if exists
rm -rf /var/www/backend/public/storage

# Create storage symlink
php /var/www/backend/artisan storage:link

# Start Redis server in background
redis-server --daemonize yes

# Wait for Redis to start
sleep 1

# Start PHP-FPM in background
php-fpm -D

# Wait for PHP-FPM to fully start
sleep 3

# Check if PHP-FPM is running
if ! pgrep -f "php-fpm" > /dev/null; then
    echo "PHP-FPM failed to start, starting in foreground..."
    php-fpm &
    sleep 2
fi

# Start Horizon in background
php /var/www/backend/artisan horizon &

# Wait for Horizon to start
sleep 2

# Check if Horizon is running
if ! pgrep -f "artisan horizon" > /dev/null; then
    echo "Horizon failed to start"
fi

# Start Nginx in foreground (keeps container alive)
nginx -g "daemon off;"