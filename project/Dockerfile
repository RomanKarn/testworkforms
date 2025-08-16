# Используем multi-stage: собираем node_modules и composer vendor, затем копируем в финальный php-apache образ

# --- node stage (если есть package.json)
FROM node:18 AS node_builder
WORKDIR /app

# Копируем весь код (нужно для сборки фронтенда, если есть)
COPY . .
# Выполните сборку фронтенда, если есть скрипт
RUN if [ -f package.json ] && grep -q "\"build\"" package.json 2>/dev/null; then npm run build || true; fi


# --- composer stage (если есть composer.json)
FROM composer:2 AS composer_builder
WORKDIR /app
# Копируем composer файлы и дёргаем vendor
COPY composer.json composer.lock* ./
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; fi

# Копируем весь проект (нужно чтобы vendor попал рядом с остальным кодом)
COPY . .

# Забираем node_modules из node_builder (если были)
COPY --from=node_builder /app/node_modules ./node_modules


# --- финальный php/apache образ
FROM php:8.1-apache
WORKDIR /var/www/html

# системные зависимости (подкорректируйте, если не нужно)
RUN apt-get update && apt-get install -y --no-install-recommends \
    zip unzip git libzip-dev \
 && docker-php-ext-install pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# Копируем приложение из composer_builder (уже с vendor/node_modules)
COPY --from=composer_builder /app /var/www/html

# Копируем entrypoint для динамической подстройки порта Apache
COPY ./project/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Права
RUN chown -R www-data:www-data /var/www/html

# Если ваше приложение ожидает порт в переменной PORT — обеспечим дефолт
ENV PORT 80

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]