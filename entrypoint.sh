#!/bin/sh

# Ensure storage and bootstrap/cache directories are writable by Apache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Run database migrations in production
php artisan migrate --force

# Start background loop for virtual account provisioning (runs every 5 minutes)
(
    while true; do
        sleep 300
        php artisan stores:provision-virtual-accounts > /dev/null 2>&1
    done
) &

# Start Apache in the foreground
exec apache2-foreground
