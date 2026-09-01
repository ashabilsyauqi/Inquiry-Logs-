#!/bin/sh
set -e

echo "🚀 [CRM Entrypoint] Initializing Difitech CRM Container..."

cd /var/www/html

# 1. Check & generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating Application Key..."
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

# 5. Clear / Cache config
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 6. Ensure Storage Permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "🌟 [CRM Entrypoint] Starting Nginx, PHP-FPM & Background Services..."
exec "$@"
