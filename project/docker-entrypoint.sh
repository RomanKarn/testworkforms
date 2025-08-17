#!/bin/bash
set -e

# ожидаем переменную PORT (Render может задать другую)
PORT=${PORT:-80}
DOCROOT="/var/www/html"

# если есть папка public, то сделаем её DocumentRoot
if [ -d "${DOCROOT}/public" ]; then
  NEW_ROOT="${DOCROOT}/public"
else
  NEW_ROOT="${DOCROOT}"
fi

# подменим DocumentRoot в конфиге Apache
APACHE_CONF="/etc/apache2/sites-available/000-default.conf"
if [ -f "$APACHE_CONF" ]; then
  sed -ri "s#(DocumentRoot\s+).*#\1${NEW_ROOT}#g" "$APACHE_CONF"
fi

# Обновим директорию в /etc/apache2/apache2.conf (директории для AllowOverride)
APACHE_MAIN_CONF="/etc/apache2/apache2.conf"
if grep -q "/var/www/" "$APACHE_MAIN_CONF"; then
  sed -ri "s#<Directory\s+/var/www/.*#<Directory ${NEW_ROOT}>#g" "$APACHE_MAIN_CONF" || true
fi

# Установим DirectoryIndex, на случай, если index.php не назначен
# Это добавит index.php,index.html
if ! grep -q "DirectoryIndex" "$APACHE_CONF" 2>/dev/null; then
  echo "DirectoryIndex index.php index.html" >> "$APACHE_CONF"
else
  sed -ri "s#DirectoryIndex .*#DirectoryIndex index.php index.html#g" "$APACHE_CONF"
fi

# Заменим Listen порт
if grep -q "^Listen " /etc/apache2/ports.conf; then
  sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
fi

# также заменим виртуальные хосты на правильный порт
for f in /etc/apache2/sites-enabled/*.conf /etc/apache2/sites-available/*.conf; do
  [ -f "$f" ] || continue
  sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/g" "$f"
done

# если нужно — установим права на папку DocumentRoot
chown -R www-data:www-data "${NEW_ROOT}"

exec "$@"