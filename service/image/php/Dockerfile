FROM php:8.1-fpm-alpine

WORKDIR /var/www

RUN mkdir /usr/local/share/composer
RUN mkdir /usr/local/share/composer/cache

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_NO_INTERACTION 1
ENV COMPOSER_HOME /usr/local/share/composer
ENV COMPOSER_CACHE_DIR /usr/local/share/composer/cache

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin/ --filename=composer

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql