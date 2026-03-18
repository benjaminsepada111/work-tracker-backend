#!/bin/sh
php artisan route:clear
php artisan cache:clear
php artisan migrate --force
exec php -S 0.0.0.0:8080 -t public
