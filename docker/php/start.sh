#!/bin/bash

# Generate Laravel application key if not exists
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Creating .env file from .env.docker..."
    cp /var/www/html/.env.docker /var/www/html/.env
fi

if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Run Laravel optimizations (with timeout)
echo "⚡ Running Laravel optimizations..."
timeout 30s php artisan config:cache || echo "Config cache timed out"
timeout 30s php artisan route:cache || echo "Route cache timed out"
timeout 30s php artisan view:cache || echo "View cache timed out"

# Create storage links if not exists
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Creating storage symbolic link..."
    php artisan storage:link
fi

# Set proper permissions
echo "🔒 Setting proper permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Clear caches (with timeout)
echo "🧹 Clearing caches..."
timeout 10s php artisan cache:clear || echo "Cache clear timed out"
timeout 10s php artisan config:clear || echo "Config clear timed out"

echo "🚀 Laravel application is ready!"

# Start PHP-FPM in foreground
echo "🔧 Starting PHP-FPM..."
exec php-fpm