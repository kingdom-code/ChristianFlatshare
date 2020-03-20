FROM php:7.2-apache

ENV APACHE_DOCUMENT_ROOT /srv/www/christianflatshare.org 
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && \
    apt-get install -y \
    zlib1g-dev\
    libxml2-dev

RUN docker-php-ext-install mysqli mbstring zip xml pdo_mysql

COPY . /srv/www/christianflatshare.org 

RUN chown -R www-data:www-data /srv/www/christianflatshare.org

EXPOSE 80
