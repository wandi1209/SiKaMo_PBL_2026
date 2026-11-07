#!/usr/bin/env bash
set -e

ROLE=${1:-web}

# Ensure storage directories exist & writable
mkdir -p \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ "$ROLE" = "web" ]; then
    php artisan storage:link --force || true
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        php artisan migrate --force
    fi

    php-fpm -D
    exec nginx -g "daemon off;"

elif [ "$ROLE" = "queue" ]; then
    exec su -s /bin/sh www-data -c "php artisan queue:work --verbose --tries=3 --timeout=90 --sleep=3 --max-time=3600"

elif [ "$ROLE" = "reverb" ]; then
    exec su -s /bin/sh www-data -c "php artisan reverb:start --host=0.0.0.0 --port=8080"

elif [ "$ROLE" = "scheduler" ]; then
    while true; do
        su -s /bin/sh www-data -c "php artisan schedule:run --verbose --no-interaction"
        sleep 60
    done

else
    exec "$@"
fi
