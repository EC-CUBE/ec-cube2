ARG PHP_VERSION_TAG=7.4
ARG TAG=${PHP_VERSION_TAG}-apache
FROM php:${TAG}

ARG GD_OPTIONS="--with-freetype --with-jpeg"
ARG EXT_INSTALL_ARGS="gd zip mysqli pgsql opcache"
ARG APCU="apcu"
# See https://github.com/debuerreotype/debuerreotype/issues/10
RUN if [ ! -d /usr/share/man/man1 ]; then mkdir /usr/share/man/man1; fi
RUN if [ ! -d /usr/share/man/man7 ]; then mkdir /usr/share/man/man7; fi

# ext-gd: libfreetype6-dev libjpeg62-turbo-dev libpng-dev
# ext-pgsql: libpq-dev
# ext-zip: libzip-dev zlib1g-dev
# ext-opcache: libpcre3-dev
RUN apt-get update \
    && apt-get install -y \
        git unzip curl apt-transport-https gnupg wget ca-certificates bc \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
        libpq-dev \
        libzip-dev zlib1g-dev \
        libpcre3-dev \
        ssl-cert \
        mariadb-client postgresql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd ${GD_OPTIONS} && docker-php-ext-install ${EXT_INSTALL_ARGS}
RUN pecl install ${APCU} && docker-php-ext-enable apcu

# composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --2.2 --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

ENV APACHE_DOCUMENT_ROOT /var/www/app/html
ENV ECCUBE_PREFIX /var/www/app
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOG_DIR /var/log/apache2

RUN mkdir -p ${APACHE_DOCUMENT_ROOT} \
  && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
  && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
  && sed -ri -e "s!DocumentRoot.*!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    ;

COPY dockerbuild/docker-php-entrypoint /usr/local/bin/

## Enable SSL
RUN a2enmod ssl rewrite headers
RUN a2ensite default-ssl
EXPOSE 443

WORKDIR ${ECCUBE_PREFIX}

COPY dockerbuild/wait-for-*.sh /
RUN chmod +x /wait-for-*.sh

COPY composer.json ${ECCUBE_PREFIX}/composer.json
COPY composer.lock ${ECCUBE_PREFIX}/composer.lock

RUN composer install --no-scripts --no-autoloader --no-dev -d ${ECCUBE_PREFIX}

COPY . ${ECCUBE_PREFIX}
RUN composer dumpautoload -o --apcu
