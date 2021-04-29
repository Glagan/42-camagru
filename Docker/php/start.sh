#!/bin/sh

echo 'Updating storage permissions...'
mkdir -p /var/www/html/storage/uploads /var/www/html/storage/tmp /var/www/html/storage/logs
chown www-data:www-data /var/www/html/storage/uploads /var/www/html/storage/tmp /var/www/html/storage/logs
echo 'Done !'
php-fpm
