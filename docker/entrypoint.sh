#!/bin/sh
set -e

echo "🚀 [CRM Entrypoint] Initializing Difitech CRM Container..."

cd /var/www/html

# 0. Ensure .env exists
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        touch .env
    fi
fi

# 1. Ensure required storage directories exist
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache

# 2. Check & generate APP_KEY if not set in .env
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generating Application Key in .env..."
    php artisan key:generate --force
fi

# 2. Wait for MySQL Database to be reachable
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "⏳ Waiting for MySQL database ($DB_HOST:$DB_PORT) to be ready..."
    until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'Connected'; exit(0); } catch (Exception \$e) { exit(1); }"; do
        echo "⏳ Database is still initializing... waiting 2s"
        sleep 2
    done
    echo "✅ Database connection established!"
fi

# 3. Create Storage Link
php artisan storage:link --quiet || true

# 4. Run Migrations & Optimize
echo "📦 Running database migrations..."
php artisan migrate --force

# Seed default brand if requested or initial startup
if [ "$RUN_SEEDER" = "true" ]; then
    echo "🌱 Running seeders..."
    php artisan db:seed --class=WkmBrandSeeder --force || true
fi

# 5. Clear / Cache config & discover in-container packages
rm -f /var/www/html/bootstrap/cache/*.php
php artisan package:discover --quiet || true
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 6. Ensure Storage Permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "🌟 [CRM Entrypoint] Starting Nginx, PHP-FPM & Background Services..."
exec "$@"
