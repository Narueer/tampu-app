#!/bin/sh
# se ejecuta cada vez que arranca el servidor en la nube
set -e

# el hosting indica el puerto en la variable PORT
PUERTO=${PORT:-80}
sed -i "s/Listen 80$/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

# los errores de laravel salen en los Logs de Render
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

php artisan config:cache
php artisan route:cache
php artisan view:cache

# crea o actualiza las tablas y carga los datos de prueba solo la primera vez
php artisan migrate --force
php artisan db:seed --force
# datos extra (galerias, mas cuartos y trabajos); si ya estan, no hace nada
php artisan db:seed --class=AmpliarDatosSeeder --force || echo "No se pudieron cargar los datos extra; la app sigue funcionando"
# 100 viviendas y 60 trabajos mas; si ya estan, no hace nada
php artisan db:seed --class=MasDatosSeeder --force || echo "No se pudieron cargar las 100 viviendas; la app sigue funcionando"

exec apache2-foreground
