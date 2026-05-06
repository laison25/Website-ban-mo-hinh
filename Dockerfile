FROM php:8.2-apache

RUN docker-php-ext-install mysqli

COPY . /var/www/html/website-ban-mo-hinh-php-v3/

RUN chown -R www-data:www-data /var/www/html/website-ban-mo-hinh-php-v3/uploads

EXPOSE 80
