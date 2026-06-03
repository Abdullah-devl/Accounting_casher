#!/bin/bash

# 1. تشغيل سيرفر قاعدة البيانات محلياً إذا كان خادم mysql متوفراً وكان الاتصال محلياً
if command -v mysql &> /dev/null && [ "$DB_CONNECTION" = "mysql" ] && { [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; }; then
    echo "Starting local MariaDB..."
    service mariadb start
    sleep 3
    mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_DATABASE:-mgasoft_db};"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USERNAME:-root}'@'localhost' IDENTIFIED BY '${DB_PASSWORD:-}';"
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO '${DB_USERNAME:-root}'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
fi

# 2. تشغيل ملفات التهيئة (Migrations)
echo "Running migrations..."
php artisan migrate --force

# 3. تشغيل ملفات التهيئة وبياناتك الافتراضية (Seeders) فقط إذا كانت قاعدة البيانات فارغة ولم يتم بذرها من قبل
echo "Checking if database is already seeded..."
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null)
USER_COUNT_CLEAN=$(echo "$USER_COUNT" | tr -dc '0-9')

if [ -z "$USER_COUNT_CLEAN" ] || [ "$USER_COUNT_CLEAN" -eq 0 ]; then
    echo "Database is empty. Seeding..."
    php artisan db:seed --force
else
    echo "Database already contains data ($USER_COUNT_CLEAN users). Skipping seed."
fi

# 4. تشغيل سيرفر لارفيل ليعمل على الإنترنت
echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=8000