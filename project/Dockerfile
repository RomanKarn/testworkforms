# Базовый образ PHP с Apache
FROM php:8.1-apache

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/* \
         && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
         && apt-get install -y --no-install-recommends nodejs \
         && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

COPY ./package*.json /var/www/html/

RUN composer install --no-dev --optimize-autoloader

RUN npm install

COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

EXPOSE 80