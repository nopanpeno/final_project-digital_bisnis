#!/bin/bash
set -e

a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

php artisan storage:link 2>/dev/null || true

php artisan schedule:work >> /var/www/html/storage/logs/scheduler.log 2>&1 &

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf