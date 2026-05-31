#!/bin/bash

# 1. تشغيل سيرفر قاعدة البيانات في الخلفية
service mariadb start

# 2. الانتظار 3 ثواني للتأكد من أن قاعدة البيانات اشتغلت تماماً
sleep 3

# 3. إنشاء قاعدة البيانات (استخدمنا الاسم الذي ظهر في خطأك السابق)
mysql -e "CREATE DATABASE IF NOT EXISTS mgasoft_db;"
mysql -e "CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY '';"
mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# 4. تشغيل ملفات التهيئة وبياناتك الافتراضية (Seeders)
php artisan migrate --force
php artisan db:seed --force

# 5. تشغيل سيرفر لارفيل ليعمل على الإنترنت
php artisan serve --host=0.0.0.0 --port=8000