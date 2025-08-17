# Фаза сборки Node.js приложения
FROM node:18 AS node_builder
WORKDIR /app

# Копируем зависимости Node.js
COPY ./package.json ./package-lock.json* ./

# Устанавливаем зависимости и собираем проект (если есть скрипт build)
RUN apt-get update && apt-get install -y && \
    npm install && npm update \
    npm run build

# Фаза сборки PHP зависимостей
FROM composer:2 AS composer_builder
WORKDIR /app

# Копируем PHP зависимости
COPY ./composer.json ./composer.lock* ./

# Устанавливаем Composer зависимости
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader;

# Копируем весь PHP проект
COPY . .

# Финальный образ с Apache
FROM php:8.1-apache
WORKDIR /var/www/html

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    zip unzip git libzip-dev libpng-dev && \
    docker-php-ext-install pdo pdo_mysql zip gd && \
    rm -rf /var/lib/apt/lists/*

# Копируем Apache конфигурацию
COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

# Включаем необходимые модули Apache
RUN a2enmod rewrite headers

# Копируем приложение из фазы Composer
COPY --from=composer_builder /app /var/www/html

# Настраиваем права
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/public

EXPOSE 80
CMD ["apache2-foreground"]