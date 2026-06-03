FROM php:8.2-cli

# تثبيت الأدوات الأساسية وإضافة سيرفر قاعدة البيانات ودعم PostgreSQL و Node.js لبناء الواجهة
RUN apt-get update -y && apt-get install -y \
    libsqlite3-dev \
    libpq-dev \
    unzip \
    zip \
    mariadb-server \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# تثبيت حزم PHP وتوليد الملفات المحسنة
RUN composer install --no-dev --optimize-autoloader

# تثبيت حزم Node وبناء ملفات الواجهة الأمامية (Vite Assets) وتطهير node_modules
RUN npm ci && npm run build && rm -rf node_modules

# نسخ ملف التشغيل وإعطائه صلاحية التنفيذ
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8000

# تشغيل السكريبت بمجرد إقلاع الحاوية
CMD ["/start.sh"]