#!/bin/bash
set -e

a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Pastikan symlink storage ada tiap container start
php artisan storage:link 2>/dev/null || true

# Jalankan Laravel scheduler di background
php artisan schedule:work >> /var/www/html/storage/logs/scheduler.log 2>&1 &

exec apache2-foreground