FROM php:7.4.16-fpm-alpine3.13

# Update and build PHP extensions
RUN apk update; apk upgrade;
RUN apk add ffmpeg curl curl-dev libpng-dev jpeg-dev libwebp-dev libzip-dev
RUN docker-php-ext-configure gd --with-jpeg --with-webp
RUN docker-php-ext-install curl pdo pdo_mysql gd zip opcache

# Add cacert for cURL
COPY cacert-2021-01-19.pem /usr/local/etc/php/cacert-2021-01-19.pem
RUN echo "curl.cainfo = /usr/local/etc/php/cacert-2021-01-19.pem" \
	>> /usr/local/etc/php/php.ini
RUN echo "openssl.cafile = /usr/local/etc/php/cacert-2021-01-19.pem" \
	>> /usr/local/etc/php/php.ini