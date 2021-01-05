#!/bin/bash
set -e

export PGPASSWORD=$DB_PASSWORD
until psql -h "${DB_SERVER}" -U "${DB_USER}" -d "template1" -c '\l'; do
  >&2 echo "Postgres is unavailable - sleeping"
  printf "."
  sleep 1
done

>&2 echo "Postgres is up - executing command"

if [ ! -f /var/www/app/data/config/config.php ]
then
    echo "Install to ec-cube"
    DBUSER=$DB_USER DBPASS=$DB_PASSWORD DBNAME=$DB_NAME DBPORT=$DB_PORT DBSERVER=$DB_SERVER /var/www/app/eccube_install.sh pgsql
fi

exec docker-php-entrypoint "$@"
