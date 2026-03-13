#!/bin/bash

# Remove broken symlink if exists
rm -rf /var/www/backend/public/storage

# Create storage symlink
php /var/www/backend/artisan storage:link

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground (keeps container alive)
nginx -g "daemon off;"