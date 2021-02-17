#!/bin/bash

set -e

echo "Waiting for mysql"
until mysql -h "${DB_SERVER}" --password="${DB_PASSWORD}" -uroot &> /dev/null
do
  printf "."
  sleep 1
done

>&2 echo "MySQL Ready"

if [ ! -f /var/www/app/data/config/config.php ]
then
    echo "Install to ec-cube"
    DBUSER=$DB_USER DBPASS=$DB_PASSWORD DBNAME=$DB_NAME DBPORT=$DB_PORT DBSERVER=$DB_SERVER /var/www/app/eccube_install.sh mysql
fi

exec docker-php-entrypoint "$@"
