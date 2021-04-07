#!/bin/sh

# First initialization if Decorations and uploads doesn't exist
if [ ! -f "/var/www/html/storage/.initialized" ]; then
	# Create folder is they still don't exist somehow
	mkdir -p /var/www/html/storage/decorations /var/www/html/storage/uploads
	# Download decorations
	wget https://ramune.nikurasu.org/camagru/decorations.zip -P /tmp
	unzip -o /tmp/decorations.zip -d /var/www/html/storage/decorations
	# Download example uploads
	wget https://ramune.nikurasu.org/camagru/uploads.zip -P /tmp
	unzip -o /tmp/uploads.zip -d /var/www/html/storage/uploads
	touch /var/www/html/storage/.initialized
fi

# Start httpd -- https://github.com/docker-library/httpd/blob/master/2.4/alpine/Dockerfile
sh /usr/local/bin/httpd-foreground