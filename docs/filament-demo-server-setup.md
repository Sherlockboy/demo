
---

```bash
# Update System & Install Essential Packages
apt update && apt upgrade -y
apt install -y software-properties-common curl git unzip

# Add PHP repository
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.3 and required extensions
apt install -y php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-sqlite3 \
    php8.3-pgsql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml \
    php8.3-bcmath php8.3-intl php8.3-readline php8.3-tokenizer php8.3-fileinfo \
    php8.3-dom php8.3-opcache

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Install Nginx
apt install -y nginx
systemctl enable nginx
systemctl start nginx

# Install SQLite
apt install -y sqlite3
```

---

```bash
# Navigate to web directory
cd /var/www

# Clone the repository
git clone https://github.com/filamentphp/demo.git filament-demo
cd filament-demo

# Set ownership
chown -R www-data:www-data /var/www/filament-demo
chmod -R 755 /var/www/filament-demo
chmod -R 775 /var/www/filament-demo/storage
chmod -R 775 /var/www/filament-demo/bootstrap/cache
mkdir -p /var/www/.cache/composer
chown -R www-data:www-data /var/www/.cache

# Install PHP dependencies
sudo -u www-data composer install --optimize-autoloader

# Install Node dependencies and build assets
sudo -u www-data npm install
sudo -u www-data npm run build

# Setup environment
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate

# Create SQLite database
sudo -u www-data touch database/database.sqlite
sudo -u www-data chmod 664 database/database.sqlite

# Update .env for SQLite
sudo -u www-data sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sudo -u www-data sed -i 's/DB_DATABASE=.*/DB_DATABASE=\/var\/www\/filament-demo\/database\/database.sqlite/' .env

# Run migrations and seeders
sudo -u www-data php artisan migrate:fresh --seed

# Cache configuration
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan filament:cache-components
sudo -u www-data php artisan icons:cache

# Link storage
sudo -u www-data php artisan storage:link
```

---

```bash
nano /etc/nginx/sites-available/filament-demo
```

```ngnix
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;  # Replace with your domain or IP
    root /var/www/filament-demo/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/filament-demo /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

```bash
nano /etc/php/8.3/fpm/pool.d/www.conf
```
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```
```bash
systemctl restart php8.3-fpm
```
