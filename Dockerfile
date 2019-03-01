FROM php:7.2-apache

RUN apt-get update && apt-get install -y wget git zip unzip zlib1g-dev curl libicu-dev g++ php- 

RUN curl --silent --show-error https://getcomposer.org/installer | php

RUN mv /var/www/html/composer.phar /usr/bin/composer && chown www-data: /var/www -R \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && rm -rf /var/lib/apt/lists/*

RUN chmod +x /usr/bin/composer

COPY ./ /var/www/html/

WORKDIR /var/www/html

RUN git clone https://github.com/SIWECOS/siwecos-crawler

WORKDIR /var/www/html/siwecos-crawler

RUN composer install

EXPOSE 80