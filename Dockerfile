ARG TAG=7.4-apache
FROM eccube2/php:${TAG}

ENV APACHE_DOCUMENT_ROOT /var/www/app/html
ENV ECCUBE_PREFIX /var/www/app
# See https://github.com/debuerreotype/debuerreotype/issues/10
RUN mkdir /usr/share/man/man1
RUN mkdir /usr/share/man/man7
RUN apt-get update \
  && apt-get install --no-install-recommends -y \
    ssl-cert \
    mariadb-client postgresql-client \
    && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

RUN mkdir -p ${APACHE_DOCUMENT_ROOT} \
  && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
  && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
  ;

## Enable SSL
RUN a2enmod ssl rewrite headers
RUN a2ensite default-ssl
EXPOSE 443

WORKDIR ${ECCUBE_PREFIX}

COPY dockerbuild/wait-for-*.sh /
RUN chmod +x /wait-for-*.sh

COPY composer.json ${ECCUBE_PREFIX}/composer.json
COPY composer.lock ${ECCUBE_PREFIX}/composer.lock

RUN composer selfupdate --2
RUN composer install --no-scripts --no-autoloader --no-dev -d ${ECCUBE_PREFIX}

COPY . ${ECCUBE_PREFIX}
RUN composer dumpautoload -o --apcu
