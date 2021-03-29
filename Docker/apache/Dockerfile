FROM httpd:2.4.46-alpine

RUN apk update; \
	apk upgrade;

# Copy apache vhost file to proxy php requests to php-fpm container
COPY camagru.apache.conf /usr/local/apache2/conf/camagru.apache.conf
RUN echo "Include /usr/local/apache2/conf/camagru.apache.conf" \
	>> /usr/local/apache2/conf/httpd.conf
