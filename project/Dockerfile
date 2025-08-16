FROM node:18 AS node_builder
WORKDIR /app

COPY . .

RUN if [ -f package.json ] && grep -q "\"build\"" package.json 2>/dev/null; then npm run build || true; fi

FROM composer:2 AS composer_builder
WORKDIR /app

COPY ./project/composer.json composer.lock* ./
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; fi

COPY . .

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