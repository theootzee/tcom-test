FROM nginx:alpine

RUN apk update \
    && apk upgrade \
    && apk --update add logrotate \
    && apk add --no-cache openssl \
    && apk add --no-cache bash

RUN apk update && apk add bash

COPY ./nginx.conf /etc/nginx/nginx.conf

COPY ./backend-not-found.html /var/www/html/backend-not-found.html

COPY ./includes/proxy.conf etc/nginx/includes