FROM php:8-apache

LABEL MAINTAINER="Andreas Kasper <andreas.kasper@goo1.de>"

RUN a2enmod headers rewrite

ADD src/html/* /var/www/html/

EXPOSE 80