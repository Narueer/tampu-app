#!/bin/sh
# se ejecuta cada vez que arranca el servidor en la nube
set -e

# el hosting indica el puerto en la variable PORT
PUERTO=${PORT:-80}
sed -i "s/Listen 80$/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache

# crea o actualiza las tablas y carga los datos de prueba solo la primera vez
php artisan migrate --force
php artisan db:seed --force

exec apache2-foreground
