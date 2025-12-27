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
}
