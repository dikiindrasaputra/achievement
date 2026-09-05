#!/bin/sh
set -e

# Generate .env from Railway env vars if not exists
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env 2>/dev/null || touch /var/www/html/.env
fi

# Set APP_URL if not provided (Railway dynamic domain)
if [ -z "$APP_URL" ]; then
    export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
    echo "APP_URL=$APP_URL" >> /var/www/html/.env
fi

# Set APP_KEY if empty
if ! grep -q "APP_KEY=" /var/www/html/.env || [ -z "$(grep 'APP_KEY=' /var/www/html/.env | cut -d'=' -f2)" ]; then
    php artisan key:generate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
