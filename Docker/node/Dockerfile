FROM node:lts-alpine

# Add wget and unzip for resources
RUN mkdir /app
RUN apk update
RUN apk upgrade
RUN apk add wget unzip

# Override Docker entrypoint
COPY start.sh /start.sh
ENTRYPOINT []
