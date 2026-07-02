#!/bin/sh

# Ensure storage and bootstrap/cache directories exist
mkdir -p /var/www/html/storage/database /var/www/html/bootstrap/cache

# Create SQLite database file inside storage/database if it doesn't exist
if [ ! -f /var/www/html/storage/database/database.sqlite ]; then
    touch /var/www/html/storage/database/database.sqlite
fi

# Set correct permissions
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations in production
php artisan migrate --force

# Start background loop for virtual account provisioning (runs every minute)
(
    while true; do
        sleep 60
        php artisan stores:provision-virtual-accounts > /dev/null 2>&1
    done
) &

# Configure Apache to listen on Render's dynamic PORT
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf
fi

# Start Apache in the foreground
exec apache2-foreground
