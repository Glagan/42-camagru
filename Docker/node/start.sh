#!/bin/sh

# First initialization if Decorations and uploads doesn't exist
if [ ! -f "/app/storage/.initialized" ]; then
	cd /app
	# Build assets
	echo 'Installing dependencies...'
	npm install
	echo 'Building assets...'
	npm run build
	# Create folder is they still don't exist somehow
	mkdir -p /app/storage/decorations /app/storage/uploads
	# Download decorations
	echo 'Downloading Decorations...'
	wget https://ramune.nikurasu.org/camagru/decorations.zip -P /tmp
	unzip -o /tmp/decorations.zip -d /app/storage/decorations
	# Download example uploads
	echo 'Downloading Uploads...'
	wget https://ramune.nikurasu.org/camagru/uploads.zip -P /tmp
	unzip -o /tmp/uploads.zip -d /app/storage/uploads
	touch /app/storage/.initialized
	echo 'Done.'
fi
