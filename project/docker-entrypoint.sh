#!/bin/bash
set -e

# если переменная PORT задана Render/окружением — подменяем конфигурацию Apache
PORT=${PORT:-80}

# заменяем Listen в /etc/apache2/ports.conf
if grep -q "^Listen " /etc/apache2/ports.conf; then
  sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
fi

# заменяем VirtualHost *:80 -> *:PORT в сайтах
for f in /etc/apache2/sites-enabled/*.conf /etc/apache2/sites-available/*.conf; do
  [ -f "$f" ] || continue
  sed -ri "s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${PORT}>/g" "$f"
done

exec "$@"