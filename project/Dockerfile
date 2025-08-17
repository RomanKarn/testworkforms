# Stage 1: node build (если есть)
FROM node:18 AS node_builder
WORKDIR /app
# копируем package*.json и весь проект (контекст должен быть project/)
COPY . .
# если есть package.json и есть скрипт build — выполнится
RUN if [ -f package.json ] && grep -q "\"build\"" package.json 2>/dev/null; then npm ci --silent --no-audit --no-fund && npm run build || true; fi

# Stage 2: composer (php deps)
FROM composer:2 AS composer_builder
WORKDIR /app

# сначала composer-зависимости (копируем только composer файлы для кеширования)
COPY ./project/composer.json composer.lock* ./
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; fi

# теперь копируем весь проект в /app
COPY . .

# скопируем результаты сборки фронта из node_builder (если есть) в /app_node
COPY --from=node_builder /app /app_node

# если в node_builder есть build или dist — переместим в /app/public (если нужно)
RUN if [ -d /app_node/build ]; then mkdir -p /app/public && cp -r /app_node/build/* /app/public/ || true; fi \
 && if [ -d /app_node/dist ]; then mkdir -p /app/public && cp -r /app_node/dist/* /app/public/ || true; fi

# Stage 3: final php/apache image
FROM php:8.1-apache
WORKDIR /var/www/html

# системные зависимости — подкорректируйте по необходимости
RUN apt-get update && apt-get install -y --no-install-recommends \
    zip unzip git libzip-dev \
 && docker-php-ext-install pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# Копируем приложение (включая vendor и public)
COPY --from=composer_builder /app /var/www/html

# Копируем entrypoint
COPY ./project/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Включаем mod_rewrite (часто нужно)
RUN a2enmod rewrite

# Права
RUN chown -R www-data:www-data /var/www/html

ENV PORT 80

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]