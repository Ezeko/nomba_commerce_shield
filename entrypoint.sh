#!/bin/sh

# Ensure storage and bootstrap/cache directories are writable by Apache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Run database migrations in production
php artisan migrate --force

# Start Apache in the foreground
exec apache2-foreground
