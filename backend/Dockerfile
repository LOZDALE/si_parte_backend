# Dockerfile aggiornato per PHP 8.2 + Apache + PDO MySQL
FROM php:8.2-apache

# Installare estensioni PHP necessarie
RUN docker-php-ext-install pdo pdo_mysql

# Imposta la cartella di lavoro
WORKDIR /var/www/html

# Copia solo la cartella public (Apache DocumentRoot)
COPY backend/public/ /var/www/html/

# Espone la porta 80
EXPOSE 80
