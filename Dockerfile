FROM php:5.4-cli

RUN echo "deb http://archive.debian.org/debian/ stretch main" > /etc/apt/sources.list \
    && echo "deb http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list

RUN apt-get update
RUN apt-get install zip git curl apt-transport-https ca-certificates ssl-cert -y --force-yes
