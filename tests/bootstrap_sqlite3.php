<?php
/**
 * PHPUnit Bootstrap for SQLite3
 *
 * SQLite3でテストを実行するための設定
 */

// SQLite3設定を強制
define('DB_TYPE', 'sqlite3');
define('DB_NAME', '/tmp/eccube_test_phpunit.db');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_SERVER', '');
define('DB_PORT', '');

echo "\n";
echo "========================================\n";
echo "  PHPUnit with SQLite3 (File-Based DB)\n";
echo "========================================\n";
echo "  DB_TYPE: ".DB_TYPE."\n";
echo "  DB_NAME: ".DB_NAME."\n";

// データベースの初期化
$dbFile = DB_NAME;
$needsInit = !file_exists($dbFile) || filesize($dbFile) == 0;

if ($needsInit) {
    echo "  [INFO] Initializing SQLite3 database...\n";

    // 既存ファイルを削除
    if (file_exists($dbFile)) {
        unlink($dbFile);
    }

    // 新しいデータベースを作成
    $sqlite = new SQLite3($dbFile);

    // スキーマファイルを読み込んで実行
    $schemaFile = __DIR__ . '/../html/install/sql/create_table_sqlite3.sql';
    if (!file_exists($schemaFile)) {
        echo "  [ERROR] Schema file not found: $schemaFile\n";
        exit(1);
    }

    $sql = file_get_contents($schemaFile);
    $statements = explode(';', $sql);
    $created = 0;

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }

        $result = @$sqlite->exec($statement . ';');
        if ($result === false) {
            echo "  [WARNING] Failed to execute statement: " . substr($statement, 0, 50) . "...\n";
            echo "           Error: " . $sqlite->lastErrorMsg() . "\n";
        } else {
            $created++;
        }
    }

    echo "  [INFO] Created $created tables/indexes\n";

    // 初期データを投入
    $dataFile = __DIR__ . '/../html/install/sql/insert_data.sql';
    if (file_exists($dataFile)) {
        echo "  [INFO] Inserting initial data...\n";
        $sql = file_get_contents($dataFile);
        // バッククォートを削除
        $sql = str_replace('`', '', $sql);
        $statements = explode(';', $sql);
        $inserted = 0;

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, 'SET ') === 0) {
                continue;
            }

            $result = @$sqlite->exec($statement . ';');
            if ($result === false) {
                // データ挿入エラーは警告のみ（一部のデータは省略可能）
                if (strpos($sqlite->lastErrorMsg(), 'UNIQUE constraint failed') === false) {
                    echo "  [WARNING] Failed to insert data: " . substr($statement, 0, 30) . "...\n";
                }
            } else {
                $inserted++;
            }
        }

        echo "  [INFO] Inserted $inserted data records\n";
    }

    $sqlite->close();
    unset($sqlite);
}

echo "========================================\n";
echo "\n";

// 通常のbootstrap読み込み
require_once __DIR__.'/require.php';
