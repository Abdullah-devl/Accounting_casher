# 1. تحديد الصورة الأساسية من Docker Hub (نستخدم PHP إصدار 8.2)
FROM php:8.2-cli

# 2. تثبيت الأدوات الأساسية وإضافة تعريفات MySQL
RUN apt-get update -y && apt-get install -y libsqlite3-dev unzip zip \
    && docker-php-ext-install pdo pdo_mysql
# 3. جلب أداة Composer (مدير حزم PHP) من صورتها الرسمية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. تحديد مجلد العمل داخل الحاوية (أي أمر قادم سيتم تنفيذه داخل هذا المجلد)
WORKDIR /app

# 5. نسخ جميع ملفات مشروعك من جهازك إلى داخل الحاوية
COPY . /app

# 6. تثبيت مكتبات لارفيل عبر Composer
RUN composer install --no-dev --optimize-autoloader

# 7. إخبار دوكر بأن هذه الحاوية ستستخدم المنفذ (البورت) 8000
EXPOSE 8000

# 8. الأمر النهائي الذي سيعمل بمجرد تشغيل الحاوية (تشغيل سيرفر لارفيل)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]