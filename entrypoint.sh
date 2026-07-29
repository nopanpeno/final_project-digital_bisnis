#!/bin/bash
set -e

a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Pastikan folder storage (termasuk yang di-mount sebagai volume) 
# punya ownership & permission yang benar untuk www-data
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Pastikan symlink storage ada tiap container start
php artisan storage:link 2>/dev/null || true

# Jalankan queue worker di background
php artisan queue:work --tries=3 >> /var/www/html/storage/logs/queue.log 2>&1 &

# Jalankan Laravel scheduler di background
php artisan schedule:work >> /var/www/html/storage/logs/scheduler.log 2>&1 &

exec apache2-foreground