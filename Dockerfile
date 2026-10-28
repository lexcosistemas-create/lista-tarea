FROM php:8.2-apache

# Instalar extensiones de PHP para SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite

# Copiar el código del proyecto al servidor web de Apache
COPY . /var/www/html/

# Dar permisos necesarios a la carpeta (vital para que SQLite pueda escribir en la base de datos)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80