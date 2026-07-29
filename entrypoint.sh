#!/bin/bash
set -e

# 1. Fix Apache MPM
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# 2. Fix Permission Storage (WAJIB sebelum storage:link)
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# 3. Link Storage
php artisan storage:link 2>/dev/null || true

# 4. Jalankan Scheduler di background (opsional, tapi aman dibiarkan)
php artisan schedule:work >> /var/www/html/storage/logs/scheduler.log 2>&1 &

# 5. Eksekusi Supervisor (ini yang akan menghidupkan Apache + Queue Worker secara stabil)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf