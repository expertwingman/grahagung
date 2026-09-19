#!/bin/bash
set -e

# Render memberi nomor port lewat variabel PORT
export PORT=${PORT:-8000}

cat > /etc/nginx/sites-available/default <<NGINX
server {
    listen ${PORT} default_server;
    root /var/www/public;
    index index.php;

    client_max_body_size 20M;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }

    location ~ \.php\$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_read_timeout 120;
        include fastcgi_params;
    }
}
NGINX

# Pastikan folder kerja Laravel ada (container punya filesystem bersih)
mkdir -p storage/framework/{views,sessions,cache/data} \
         storage/app/public storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan view:cache
# route:cache sengaja TIDAK dipakai: routes/web.php masih memakai closure,
# dan Laravel tidak bisa men-serialize closure.

# Migrate JANGAN mematikan container kalau gagal — kalau mati,
# container restart terus dan membanjiri database dengan percobaan login.
php artisan migrate --force || echo "PERINGATAN: migrate gagal, container tetap jalan"


php-fpm -D
exec nginx -g "daemon off;"
