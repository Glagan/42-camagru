FROM php:7.4.16-fpm-alpine3.13

RUN apk update; \
	apk upgrade;

RUN docker-php-ext-install pdo pdo_mysql
