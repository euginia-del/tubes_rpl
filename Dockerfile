FROM php:8.1-apache

# Tetap kita pasang drivernya biar nanti gak kerja dua kali
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy semua file (termasuk folder templates/code)
COPY ./templates/code/ /var/www/html/

    RUN chown -R www-data:www-data /var/www/html