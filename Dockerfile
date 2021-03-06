FROM php:7.2.1-apache

RUN apt-get update && apt-get install -y mysql-client \ 
      && docker-php-ext-install mysqli

RUN a2enmod rewrite

COPY DocumentRoot/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html
