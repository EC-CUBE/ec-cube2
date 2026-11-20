<?php

$url = parse_url(getenv('DATABASE_URL'));

SC_Initial_Ex::defineIfNotDefined('ECCUBE_INSTALL', 'ON');
SC_Initial_Ex::defineIfNotDefined('HTTP_URL', 'https://'.$_SERVER['SERVER_NAME'].'/');
SC_Initial_Ex::defineIfNotDefined('HTTPS_URL', 'https://'.$_SERVER['SERVER_NAME'].'/');
SC_Initial_Ex::defineIfNotDefined('ROOT_URLPATH', '/');
SC_Initial_Ex::defineIfNotDefined('DOMAIN_NAME', '');
SC_Initial_Ex::defineIfNotDefined('DB_TYPE', 'pgsql');
SC_Initial_Ex::defineIfNotDefined('DB_USER', $url['user']);
SC_Initial_Ex::defineIfNotDefined('DB_PASSWORD', $url['pass']);
SC_Initial_Ex::defineIfNotDefined('DB_SERVER', $url['host']);
SC_Initial_Ex::defineIfNotDefined('DB_NAME', substr($url['path'], 1));
SC_Initial_Ex::defineIfNotDefined('DB_PORT', '5432');
SC_Initial_Ex::defineIfNotDefined('ADMIN_DIR', 'admin/');
SC_Initial_Ex::defineIfNotDefined('ADMIN_FORCE_SSL', false);
SC_Initial_Ex::defineIfNotDefined('ADMIN_ALLOW_HOSTS', 'a:0:{}');
SC_Initial_Ex::defineIfNotDefined('AUTH_MAGIC', 'droucliuijeanamiundpnoufrouphudrastiokec');
SC_Initial_Ex::defineIfNotDefined('PASSWORD_HASH_ALGOS', PASSWORD_DEFAULT);
SC_Initial_Ex::defineIfNotDefined('MAIL_BACKEND', 'mail');
SC_Initial_Ex::defineIfNotDefined('SMTP_HOST', '');
SC_Initial_Ex::defineIfNotDefined('SMTP_PORT', '');
SC_Initial_Ex::defineIfNotDefined('SMTP_USER', '');
SC_Initial_Ex::defineIfNotDefined('SMTP_PASSWORD', '');
