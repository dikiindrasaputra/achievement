#!/bin/sh
set -e

# Remove old .env if exists, regenerate from Railway env vars
rm -f /var/www/html/.env
touch /var/www/html/.env

# Build .env from Railway environment variables
printenv | grep -E "^(APP_|DB_|SESSION_|CACHE_|QUEUE_|LOG_|MAIL_|REDIS_|BCRYPT_|BROADCAST_|FILESYSTEM_|GOOGLE_)" | sed 's/^/export /' >> /var/www/html/.env

# Set default APP_URL if not provided
if ! grep -q "APP_URL" /var/www/html/.env; then
    echo "APP_URL=https://${RAILWAY_PUBLIC_DOMAIN}" >> /var/www/html/.env
fi

php artisan config:clear
php artisan config:cache
php artisan route:cache

php artisan migrate --force

php artisan view:clear || true

php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
