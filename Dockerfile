FROM eccube2/php:7.4-apache

ENV APACHE_DOCUMENT_ROOT /var/www/app/html
ENV ECCUBE_PREFIX /var/www/app

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

COPY . ${ECCUBE_PREFIX}

USER www-data

RUN composer install \
  --no-scripts \
  --no-autoloader \
  -d ${ECCUBE_PREFIX} \
  ;

RUN composer dumpautoload -o --apcu

# trueを指定した場合、DBマイグレーションやECCubeのキャッシュ作成をスキップする。
# ビルド時点でDBを起動出来ない場合等に指定が必要となる。
ARG SKIP_INSTALL_SCRIPT_ON_DOCKER_BUILD=false
ARG DBTYPE=mysql
ARG DBSERVER=db
ARG DBUSER=eccube_db_user
ARG DBPASS=password
ARG DBNAME=eccube_db
ARG HTTP_URL=https://localhost:4430
ARG HTTPS_URL=https://localhost:4430

RUN if [ ! -f ${ECCUBE_PREFIX}/data/config/config.php ] && [ ! ${SKIP_INSTALL_SCRIPT_ON_DOCKER_BUILD} = "true" ]; then \
        ${ECCUBE_PREFIX}/eccube_install.sh ${DBTYPE} \
        ; fi
