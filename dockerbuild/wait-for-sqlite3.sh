#!/bin/bash

set -e

echo "Using SQLite3"

if [ ! -f /var/www/app/data/config/config.php ]
then
    echo "Install to ec-cube with SQLite3"
    DBNAME="$DB_NAME" /var/www/app/eccube_install.sh sqlite3

    # Enable WAL mode for better concurrency (allows reads during write transactions)
    echo "Enabling SQLite3 WAL mode..."
    php -r '
    $db = new SQLite3(getenv("DB_NAME"));
    $db->exec("PRAGMA journal_mode=WAL;");
    $db->close();
    echo "WAL mode enabled" . PHP_EOL;
    '
fi

# Ensure the database file and its directory are writable by Apache (www-data)
chmod o+w "$DB_NAME"
chmod o+w "$(dirname "$DB_NAME")"

>&2 echo "SQLite3 Ready"

exec docker-php-entrypoint "$@"
