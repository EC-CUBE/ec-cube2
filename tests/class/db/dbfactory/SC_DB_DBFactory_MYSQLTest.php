<?php

class SC_DB_DBFactory_MYSQLTest extends SC_DB_DBFactoryTestAbstract
{
    /**
     * @var SC_DB_DBFactory_MYSQL
     */
    protected $dbFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbFactory = new SC_DB_DBFactory_MYSQL_Ex();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_DB_DBFactory_MYSQL_Ex', $this->dbFactory);
    }

    public function testGetTransactionIsolationLevel()
    {
        $this->assertEquals(SC_DB_DBFactory_Ex::ISOLATION_LEVEL_REPEATABLE_READ, $this->dbFactory->getTransactionIsolationLevel());
    }

    public function testIsSkipDeleteIfNotExists()
    {
        $this->assertTrue($this->dbFactory->isSkipDeleteIfNotExists());
    }

    public function testSfChangeReservedWords☛WithRank()
    {
        $sql = <<< __EOS__
            SELECT rank, (rank), RANK, Rank, `rank`, RANK() OVER (ORDER BY rank), RANK
                \t() OVER (ORDER BY foorank), rank.rank, (SELECT MAX(rankfoo) AS rank FROM rank) AS rankbar
                ,rank+1, rank-(1), \$rank\$,
                -- 以下はSQL文としては無効だが確認のため
                #rank#
            FROM rank
            ORDER BY rank
            __EOS__;

        $expected = <<< __EOS__
            SELECT `rank`, (`rank`), `RANK`, `Rank`, `rank`, RANK() OVER (ORDER BY `rank`), RANK
                \t() OVER (ORDER BY foorank), rank.rank, (SELECT MAX(rankfoo) AS `rank` FROM `rank`) AS rankbar
                ,`rank`+1, `rank`-(1), \$rank\$,
                -- 以下はSQL文としては無効だが確認のため
                #`rank`#
            FROM `rank`
            ORDER BY `rank`
            __EOS__;

        $this->assertSame($expected, $this->dbFactory->sfChangeReservedWords($sql));
    }

    public function testSfChangeReservedWords☛WithRank末尾空白()
    {
        $sql = 'ORDER BY rank ';
        $expected = 'ORDER BY `rank` ';

        $this->assertSame($expected, $this->dbFactory->sfChangeReservedWords($sql));
    }

    public function testAddLimitOffset()
    {
        $sql_base = 'SELECT foo FROM bar ORDER BY boo';

        $this->assertSame(
            "{$sql_base} LIMIT 2",
            $this->dbFactory->addLimitOffset($sql_base, 2)
        );

        $this->assertSame(
            "{$sql_base} LIMIT 18446744073709551615 OFFSET 3",
            $this->dbFactory->addLimitOffset($sql_base, null, 3)
        );

        $this->assertSame(
            "{$sql_base} LIMIT 2 OFFSET 3",
            $this->dbFactory->addLimitOffset($sql_base, 2, 3)
        );
    }

    /**
     * listTables()が大文字小文字を保持することを確認
     */
    public function testListTables保持大文字小文字()
    {
        if (DB_TYPE !== 'mysql' && DB_TYPE !== 'mysqli') {
            $this->markTestSkipped('This test is only for MySQL/MySQLi');
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        // 大文字を含むテストテーブルを作成
        $testTableName = 'plg_TestMixedCase';
        $objQuery->query("CREATE TABLE {$testTableName} (id INT PRIMARY KEY)");

        try {
            // テーブル一覧を取得
            $tables = $objQuery->listTables();

            // テストテーブルが元の大文字小文字で含まれることを確認
            $this->assertContains(
                $testTableName,
                $tables,
                'listTables() should preserve table name case sensitivity'
            );

            // 全て大文字のテーブル名が含まれていないことを確認
            $this->assertNotContains(
                strtoupper($testTableName),
                $tables,
                'listTables() should not uppercase table names'
            );
        } finally {
            // テストテーブルを削除
            $objQuery->query("DROP TABLE IF EXISTS {$testTableName}");
        }
    }

    /**
     * listTables()がシステムテーブルを除外することを確認
     */
    public function testListTablesシステムテーブル除外()
    {
        if (DB_TYPE !== 'mysql' && DB_TYPE !== 'mysqli') {
            $this->markTestSkipped('This test is only for MySQL/MySQLi');
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();
        $tables = $objQuery->listTables();

        // EC-CUBEのテーブルが含まれることを確認
        $hasDtbTable = false;
        foreach ($tables as $table) {
            if (str_starts_with($table, 'dtb_')) {
                $hasDtbTable = true;
                break;
            }
        }
        $this->assertTrue($hasDtbTable, 'Should include EC-CUBE tables (dtb_*)');

        // information_schemaのテーブルが含まれないことを確認
        foreach ($tables as $table) {
            $this->assertStringStartsNotWith('COLUMNS', $table);
            $this->assertStringStartsNotWith('TABLES', $table);
            $this->assertStringStartsNotWith('SCHEMATA', $table);
        }
    }

    /**
     * order_birth が create_date と同年かつ RIGHT(create_date, 5) < RIGHT(order_birth, 5) の場合に
     * BIGINT UNSIGNED オーバーフローが発生しないことを確認する.
     *
     * MySQL の datetime 型では RIGHT(col, 5) は時刻の MM:SS 部分を返す.
     * YEAR差が 0 のとき、order_birth の MM:SS が create_date より大きいと
     * 0 - 1 = -1 となり unsigned 演算でオーバーフローする.
     *
     * @see https://github.com/EC-CUBE/ec-cube2/pull/1350
     */
    public function testGetOrderTotalAgeColSqlは同年で誕生日未到来でもオーバーフローしない()
    {
        if (DB_TYPE !== 'mysql' && DB_TYPE !== 'mysqli') {
            $this->markTestSkipped('This test is only for MySQL/MySQLi');
        }

        $customer_id = $this->objGenerator->createCustomer();
        $order_id = $this->objQuery->nextVal('dtb_order_order_id');
        // create_date の MM:SS=00:00, order_birth の MM:SS=30:00
        // → RIGHT(create_date,5) < RIGHT(order_birth,5) が TRUE
        // → YEAR差 0 から 1 を引いて -1 → 修正前は BIGINT UNSIGNED オーバーフロー
        $this->objQuery->insert('dtb_order', [
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'order_birth' => '2026-01-15 10:30:00',
            'create_date' => '2026-02-01 08:00:00',
            'status' => ORDER_NEW,
            'del_flg' => 0,
            'total' => 1000,
            'subtotal' => 1000,
            'tax' => 100,
            'payment_total' => 1000,
        ]);

        $col = $this->dbFactory->getOrderTotalAgeColSql().' AS age';
        $col .= ',COUNT(order_id) AS order_count';
        $col .= ',SUM(total) AS total';
        $col .= ',AVG(total) AS total_average';

        $this->objQuery->setGroupBy('age');
        $this->objQuery->setOrder('age DESC');
        $result = $this->objQuery->select($col, 'dtb_order', 'order_id = ?', [$order_id]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // ============================================================
    // sfChangeArrayToString
    // ============================================================

    public function testSfChangeArrayToStringはARRAYTOSTRINGをGROUPCONCATに変換する()
    {
        $sql = "SELECT ARRAY_TO_STRING(ARRAY(SELECT name FROM users WHERE id = 1), ',') FROM dual";
        $result = $this->dbFactory->sfChangeArrayToString($sql);

        $this->assertStringNotContainsString('ARRAY_TO_STRING', $result);
        $this->assertStringNotContainsString('ARRAY(', $result);
        $this->assertStringContainsString('GROUP_CONCAT', $result);
        $this->assertStringContainsString("SEPARATOR ','", $result);
    }

    public function testSfChangeArrayToStringは複数のARRAYTOSTRINGを変換する()
    {
        $sql = "SELECT ARRAY_TO_STRING(ARRAY(SELECT name FROM users WHERE id = 1), ','), ARRAY_TO_STRING(ARRAY(SELECT email FROM users WHERE id = 2), ';')";
        $result = $this->dbFactory->sfChangeArrayToString($sql);

        $this->assertStringNotContainsString('ARRAY_TO_STRING', $result);
        $this->assertEquals(2, substr_count($result, 'GROUP_CONCAT'));
    }

    public function testSfChangeArrayToStringはARRAYTOSTRINGがない場合はそのまま返す()
    {
        $sql = 'SELECT * FROM users WHERE id = 1';
        $result = $this->dbFactory->sfChangeArrayToString($sql);

        $this->assertSame($sql, $result);
    }

    /**
     * プラグインテーブル（plg_*）を含むことを確認
     */
    public function testListTablesプラグインテーブル対応()
    {
        if (DB_TYPE !== 'mysql' && DB_TYPE !== 'mysqli') {
            $this->markTestSkipped('This test is only for MySQL/MySQLi');
        }

        $objQuery = SC_Query_Ex::getSingletonInstance();

        // プラグインテーブルを作成
        $pluginTableName = 'plg_SamplePlugin';
        $objQuery->query("CREATE TABLE {$pluginTableName} (
            plugin_id INT PRIMARY KEY AUTO_INCREMENT,
            plugin_name VARCHAR(255)
        )");

        try {
            $tables = $objQuery->listTables();

            // プラグインテーブルが含まれることを確認
            $this->assertContains(
                $pluginTableName,
                $tables,
                'Plugin tables (plg_*) should be included in the list'
            );
        } finally {
            $objQuery->query("DROP TABLE IF EXISTS {$pluginTableName}");
        }
    }
}
