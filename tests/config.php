<?php
$url = parse_url(getenv('DATABASE_URL'));

define('ECCUBE_INSTALL', 'ON');
define('HTTP_URL', 'https://'.$_SERVER['SERVER_NAME'].'/');
define('HTTPS_URL', 'https://'.$_SERVER['SERVER_NAME'].'/');
define('ROOT_URLPATH', '/');
define('DOMAIN_NAME', '');
define('DB_TYPE', 'pgsql');
define('DB_USER', $url['user']);
define('DB_PASSWORD', $url['pass']);
define('DB_SERVER', $url['host']);
define('DB_NAME', substr($url['path'], 1));
define('DB_PORT', '5432');
define('ADMIN_DIR', 'admin/');
define('ADMIN_FORCE_SSL', FALSE);
define('ADMIN_ALLOW_HOSTS', 'a:0:{}');
define('AUTH_MAGIC', 'droucliuijeanamiundpnoufrouphudrastiokec');
define('PASSWORD_HASH_ALGOS', 'sha256');
define('MAIL_BACKEND', 'mail');
define('SMTP_HOST', '');
define('SMTP_PORT', '');
define('SMTP_USER', '');
define('SMTP_PASSWORD', '');
