#!/bin/bash

set -e

echo "Using SQLite3"

if [ ! -f /var/www/app/data/config/config.php ]
then
    echo "Install to ec-cube with SQLite3"
    DBNAME=$DB_NAME /var/www/app/eccube_install.sh sqlite3
fi

>&2 echo "SQLite3 Ready"

exec docker-php-entrypoint "$@"
