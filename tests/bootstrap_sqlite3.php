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

// DB接続テスト
echo "  [INFO] Testing MDB2 connection...\n";
require_once __DIR__ . '/../data/vendor/nanasess/mdb2/MDB2.php';

$testDsn = [
    'phptype' => 'sqlite3',
    'username' => '',
    'password' => '',
    'protocol' => 'tcp',
    'hostspec' => '',
    'port' => '',
    'database' => DB_NAME,
];

$testConn = MDB2::connect($testDsn);
if (PEAR::isError($testConn)) {
    echo "  [ERROR] MDB2 connection failed!\n";
    echo "  Error: " . $testConn->getMessage() . "\n";
    echo "  Debug: " . $testConn->getDebugInfo() . "\n";
    exit(1);
}

echo "  [INFO] MDB2 connected successfully!\n";

// 簡単なクエリテスト
$result = $testConn->query('SELECT COUNT(*) FROM dtb_member');
if (PEAR::isError($result)) {
    echo "  [ERROR] Query failed: " . $result->getMessage() . "\n";
} else {
    $row = $result->fetchRow();
    echo "  [INFO] dtb_member count: " . $row[0] . "\n";
}

$testConn->disconnect();
echo "========================================\n";
echo "\n";

// 通常のbootstrap読み込み（必要な定数も定義される）
require_once __DIR__.'/require.php';

// SC_Query経由のテスト
echo "\n";
echo "========================================\n";
echo "  Testing SC_Query with SQLite3...\n";
echo "========================================\n";

$objQuery = SC_Query_Ex::getSingletonInstance();
echo "  [INFO] SC_Query instance created\n";

try {
    $count = $objQuery->count('dtb_member');
    echo "  [INFO] SC_Query test passed! dtb_member count: $count\n";
} catch (Exception $e) {
    echo "  [ERROR] SC_Query test failed: " . $e->getMessage() . "\n";
}

echo "========================================\n";
echo "\n";
