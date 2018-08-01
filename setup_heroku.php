<?php
$url = parse_url(getenv('DATABASE_URL'));

$stdout= fopen('php://stdout', 'w');
fwrite( $stdout, print_r($url)."\n" );

$cmd = 'export DBSERVER="'.$url['host'].'";';
$cmd .= 'export DBUSER="'.$url['user'].'";';
$cmd .= 'export DBPASS="'.$url['pass'].'";';
$cmd .= 'export DBNAME="'.substr($url['path'], 1).'";';

$app = getenv('HEROKU_APP_NAME');
$cmd .= 'export HTTP_URL="http://'.$app.'.herokuapp.com/";';
$cmd .= 'export HTTPS_URL="https://'.$app.'.herokuapp.com/";';

$cmd .= ' sh ./eccube_install.sh heroku';

$stdout= fopen('php://stdout', 'w');
fwrite( $stdout, $cmd."\n" );

echo "<pre>".shell_exec($cmd)."</pre>";

// sql
$dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
$pdo = new PDO($dsn, $url['user'], $url['pass']);
$sql = "UPDATE mtb_constants SET name = 'true' WHERE id = 'DEBUG_MODE'";
$count = $pdo->exec($sql);
echo "{$count}件データを更新しました。".PHP_EOL;
