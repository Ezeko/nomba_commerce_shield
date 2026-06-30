#!/usr/bin/env bash
# exit on error
set -o errexit

echo ">>> Running composer install..."
composer install --no-dev --optimize-autoloader

echo ">>> Running npm install..."
npm install

echo ">>> Running npm run build (Vite compile)..."
npm run build

echo ">>> Creating SQLite database if not exists..."
mkdir -p database
touch database/database.sqlite

echo ">>> Running migrations..."
php artisan migrate --force
echo ">>> Build script completed successfully!"
