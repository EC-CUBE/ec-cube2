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
