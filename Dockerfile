FROM php:8.2-cli

# تثبيت الأدوات الأساسية وإضافة سيرفر قاعدة البيانات (mariadb-server)
RUN apt-get update -y && apt-get install -y \
    libsqlite3-dev \
    unzip \
    zip \
    mariadb-server \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install --no-dev --optimize-autoloader

# نسخ ملف التشغيل وإعطائه صلاحية التنفيذ
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8000

# تشغيل السكريبت بمجرد إقلاع الحاوية
CMD ["/start.sh"]