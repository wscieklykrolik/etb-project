#!/bin/sh
set -eu

php artisan migrate --force
php artisan storage:link --force
php artisan config:cache
php artisan view:cache
php artisan route:cache

exec apache2-foreground
