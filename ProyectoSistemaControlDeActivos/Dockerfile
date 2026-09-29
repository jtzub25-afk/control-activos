FROM php:8.2-apache

# Instalar los drivers necesarios para conectarse a PostgreSQL (Supabase)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar todos los archivos de tu proyecto al servidor web
COPY . /var/www/html/

# Crear carpeta de respaldo uploads y dar permisos de escritura
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html

EXPOSE 80