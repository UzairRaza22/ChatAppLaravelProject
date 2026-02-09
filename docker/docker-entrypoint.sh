#!/bin/bash

# Wait for MongoDB to be ready
echo "🔄 Waiting for MongoDB to be ready..."
while ! nc -z mongodb 27017; do
  sleep 1
done
echo "✅ MongoDB is ready!"

# Generate Laravel application key if not exists
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Creating .env file from .env.docker..."
    cp /var/www/html/.env.docker /var/www/html/.env
fi

if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Run Laravel optimizations
echo "⚡ Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage links if not exists
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Creating storage symbolic link..."
    php artisan storage:link
fi

# Set proper permissions
echo "🔒 Setting proper permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Clear any existing cache
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear

echo "🚀 Laravel application is ready!"

# Start the default command
exec "$@"
