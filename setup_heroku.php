<?php
putenv("DB=pgsql");
putenv("DBSERVER=".getenv('DB_HOST'));
putenv("DBNAME=".getenv('DB_DATABASE'));
putenv("USER=".getenv('DB_USERNAME'));
putenv("DBPASS=".getenv('DB_PASSWORD'));
putenv("HTTP_URL=http://".getenv('HEROKU_APP_NAME').".herokuapp.com");
putenv("HTTPS_URL=http://".getenv('HEROKU_APP_NAME').".herokuapp.com");

exec("sh ./eccube_install.sh");
