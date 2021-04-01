FROM php:7.4.16-fpm-alpine3.13

RUN apk update; \
	apk upgrade; \
	apk add curl curl-dev libpng-dev jpeg-dev libwebp-dev libzip-dev;

RUN docker-php-ext-install curl pdo pdo_mysql gd zip opcache
COPY cacert-2021-01-19.pem /usr/local/etc/php/cacert-2021-01-19.pem
RUN echo "curl.cainfo = /usr/local/etc/php/cacert-2021-01-19.pem" \
	>> /usr/local/etc/php/php.ini
RUN echo "openssl.cafile = /usr/local/etc/php/cacert-2021-01-19.pem" \
	>> /usr/local/etc/php/php.ini