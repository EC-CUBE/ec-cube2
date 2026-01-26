<?php

class SC_DB_DBFactory_SQLITE3Test extends SC_DB_DBFactoryTestAbstract
{
    /**
     * @var SC_DB_DBFactory_SQLITE3
     */
    protected $dbFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbFactory = SC_DB_DBFactory_Ex::getInstance('sqlite3');
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_DB_DBFactory_SQLITE3', $this->dbFactory);
    }

    // ============================================================
    // addLimitOffset
    // ============================================================

    public function testAddLimitOffset()
    {
        $sql = 'SELECT foo FROM bar ORDER BY boo';

        $this->assertSame(
            "{$sql} LIMIT 2",
            $this->dbFactory->addLimitOffset($sql, 2)
        );

        // SQLite3 では OFFSET 指定時に LIMIT が必須 → 自動で LIMIT -1 が付く
        $this->assertSame(
            "{$sql} LIMIT -1 OFFSET 3",
            $this->dbFactory->addLimitOffset($sql, null, 3)
        );

        $this->assertSame(
            "{$sql} LIMIT 2 OFFSET 3",
            $this->dbFactory->addLimitOffset($sql, 2, 3)
        );
    }

    // ============================================================
    // sfChangeMySQL — SQL 方言変換
    // ============================================================

    public function testSfChangeMySQLはILIKEをLIKEに変換する()
    {
        $sql = "SELECT * FROM dtb_customer WHERE name01 ILIKE '%山口%'";
        $result = $this->dbFactory->sfChangeMySQL($sql);

        $this->assertStringNotContainsString('ILIKE', $result);
        $this->assertStringContainsString('LIKE', $result);
    }

    public function testSfChangeMySQLはCurrentTimestampをローカルタイムに変換する()
    {
        $sql = 'INSERT INTO dtb_session VALUES(CURRENT_TIMESTAMP)';
        $result = $this->dbFactory->sfChangeMySQL($sql);

        $this->assertStringNotContainsString('CURRENT_TIMESTAMP', $result);
        $this->assertStringContainsString("datetime('now','localtime')", $result);
    }

    public function testSfChangeMySQLはNowをローカルタイムに変換する()
    {
        $sql = 'UPDATE dtb_member SET update_date = Now() WHERE member_id = 1';
        $result = $this->dbFactory->sfChangeMySQL($sql);

        $this->assertStringNotContainsString('Now()', $result);
        $this->assertStringContainsString("datetime('now','localtime')", $result);
    }

    public function testSfChangeMySQLはEXTRACTをStrftimeに変換する()
    {
        $sql = 'SELECT EXTRACT(month FROM birth) AS birth_month FROM dtb_customer';
        $result = $this->dbFactory->sfChangeMySQL($sql);

        $this->assertStringNotContainsString('EXTRACT', $result);
        $this->assertStringContainsString("strftime('%m'", $result);
        $this->assertStringContainsString('CAST(', $result);
    }

    public function testSfChangeExtractは各フィールドを正しく変換する()
    {
        $fields = [
            'year' => '%Y',
            'month' => '%m',
            'day' => '%d',
            'hour' => '%H',
            'minute' => '%M',
            'second' => '%S',
        ];

        foreach ($fields as $field => $format) {
            $sql = "SELECT EXTRACT({$field} FROM create_date) FROM dtb_order";
            $result = $this->dbFactory->sfChangeExtract($sql);
            $this->assertStringContainsString("strftime('{$format}'", $result, "{$field} の変換が正しくない");
        }
    }

    public function testSfChangeMySQLはSerialPrimaryKeyを変換する()
    {
        $sql = 'CREATE TABLE test (id SERIAL PRIMARY KEY, name TEXT)';
        $result = $this->dbFactory->sfChangeMySQL($sql);

        $this->assertStringContainsString('INTEGER PRIMARY KEY AUTOINCREMENT', $result);
    }

    // ============================================================
    // concatColumn
    // ============================================================

    public function testConcatColumnは2カラムを連結する()
    {
        $result = $this->dbFactory->concatColumn(['name01', 'name02']);
        $this->assertSame('name01 || name02', $result);
    }

    public function testConcatColumnは3カラムを連結する()
    {
        $result = $this->dbFactory->concatColumn(['tel01', 'tel02', 'tel03']);
        $this->assertSame('tel01 || tel02 || tel03', $result);
    }

    public function testConcatColumnは1カラムの場合そのまま返す()
    {
        $result = $this->dbFactory->concatColumn(['name01']);
        $this->assertSame('name01', $result);
    }

    // ============================================================
    // getDummyFromClauseSql
    // ============================================================

    public function testGetDummyFromClauseSqlは空文字を返す()
    {
        $this->assertSame('', $this->dbFactory->getDummyFromClauseSql());
    }

    // ============================================================
    // sfGetColumnList
    // ============================================================

    public function testSfGetColumnListはカラム一覧を返す()
    {
        if (DB_TYPE !== 'sqlite3') {
            $this->markTestSkipped('SQLite3 環境でのみ実行');
        }

        $columns = $this->dbFactory->sfGetColumnList('dtb_member');
        $this->assertContains('member_id', $columns);
        $this->assertContains('login_id', $columns);
        $this->assertContains('password', $columns);
    }

    // ============================================================
    // getOrderYesterdaySql / getOrderMonthSql / getReviewYesterdaySql
    // ============================================================

    public function testGetOrderYesterdaySqlはSQLを返す()
    {
        $sql = $this->dbFactory->getOrderYesterdaySql('SUM');
        $this->assertStringContainsString('SUM(total)', $sql);
        $this->assertStringContainsString("date('now', 'localtime', '-1 day')", $sql);
    }

    public function testGetOrderMonthSqlはSQLを返す()
    {
        $sql = $this->dbFactory->getOrderMonthSql('COUNT');
        $this->assertStringContainsString('COUNT(total)', $sql);
        $this->assertStringContainsString("strftime('%Y/%m', create_date)", $sql);
    }

    public function testGetReviewYesterdaySqlはSQLを返す()
    {
        $sql = $this->dbFactory->getReviewYesterdaySql();
        $this->assertStringContainsString('COUNT(*)', $sql);
        $this->assertStringContainsString("date('now', 'localtime', '-1 day')", $sql);
    }

    // ============================================================
    // getSendHistoryWhereStartdateSql
    // ============================================================

    public function testGetSendHistoryWhereStartdateSqlはBETWEENを返す()
    {
        $sql = $this->dbFactory->getSendHistoryWhereStartdateSql();
        $this->assertStringContainsString('BETWEEN', $sql);
        $this->assertStringContainsString("datetime('now', 'localtime'", $sql);
    }

    // ============================================================
    // getOrderTotalDaysWhereSql
    // ============================================================

    public function testGetOrderTotalDaysWhereSqlはデフォルトで日別フォーマットを返す()
    {
        $sql = $this->dbFactory->getOrderTotalDaysWhereSql('day');
        $this->assertStringContainsString("strftime('%Y-%m-%d', create_date) AS str_date", $sql);
        $this->assertStringContainsString('COUNT(order_id) AS total_order', $sql);
    }

    public function testGetOrderTotalDaysWhereSqlは月別フォーマットを返す()
    {
        $sql = $this->dbFactory->getOrderTotalDaysWhereSql('month');
        $this->assertStringContainsString("strftime('%Y-%m', create_date) AS str_date", $sql);
    }

    public function testGetOrderTotalDaysWhereSqlは年別フォーマットを返す()
    {
        $sql = $this->dbFactory->getOrderTotalDaysWhereSql('year');
        $this->assertStringContainsString("strftime('%Y', create_date) AS str_date", $sql);
    }

    public function testGetOrderTotalDaysWhereSqlは時間別フォーマットを返す()
    {
        $sql = $this->dbFactory->getOrderTotalDaysWhereSql('hour');
        $this->assertStringContainsString("strftime('%H', create_date) AS str_date", $sql);
    }

    public function testGetOrderTotalDaysWhereSqlは曜日別でCASE式を使う()
    {
        $sql = $this->dbFactory->getOrderTotalDaysWhereSql('wday');
        $this->assertStringContainsString('CASE', $sql);
        $this->assertStringContainsString("'Mon'", $sql);
        $this->assertStringContainsString("'Sun'", $sql);
        $this->assertStringContainsString('AS str_date', $sql);
    }

    // ============================================================
    // getOrderTotalAgeColSql
    // ============================================================

    public function testGetOrderTotalAgeColSqlは年代算出SQLを返す()
    {
        $sql = $this->dbFactory->getOrderTotalAgeColSql();
        $this->assertStringContainsString("strftime('%Y', create_date)", $sql);
        $this->assertStringContainsString("strftime('%Y', order_birth)", $sql);
        $this->assertStringContainsString("strftime('%m-%d'", $sql);
        $this->assertStringContainsString('/ 10', $sql);
        $this->assertStringContainsString('* 10', $sql);
    }

    // ============================================================
    // getDownloadableDaysWhereSql
    // ============================================================

    public function testGetDownloadableDaysWhereSqlはSQLを返す()
    {
        $sql = $this->dbFactory->getDownloadableDaysWhereSql('T1');
        $this->assertStringContainsString('T1.payment_date', $sql);
        $this->assertStringContainsString("date('now', 'localtime')", $sql);
    }
}
