FROM httpd:2.4.46-alpine

RUN apk update
RUN apk upgrade
RUN apk add wget unzip

# Copy apache vhost file to proxy php requests to php-fpm container
COPY camagru.apache.conf /usr/local/apache2/conf/camagru.apache.conf
RUN echo "Include /usr/local/apache2/conf/camagru.apache.conf" \
	>> /usr/local/apache2/conf/httpd.conf

# Script to download resources on startup
COPY start.sh /start.s
ENTRYPOINT []
