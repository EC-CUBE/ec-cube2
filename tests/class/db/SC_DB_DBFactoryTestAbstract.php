<?php

class SC_DB_DBFactoryTestAbstract extends Common_TestCase
{
    /**
     * @var SC_DB_DBFactory
     */
    protected $dbFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbFactory = SC_DB_DBFactory_Ex::getInstance();
    }

    public function testGetTransactionIsolationLevel()
    {
        $this->assertEquals(SC_DB_DBFactory_Ex::ISOLATION_LEVEL_READ_COMMITTED, $this->dbFactory->getTransactionIsolationLevel());
    }

    public function testIsSkipDeleteIfNotExists()
    {
        $this->assertFalse($this->dbFactory->isSkipDeleteIfNotExists());
    }

    public function testAddLimitOffset()
    {
        $sql_base = 'SELECT foo FROM bar ORDER BY boo';

        $this->assertSame(
            "{$sql_base} LIMIT 2",
            $this->dbFactory->addLimitOffset($sql_base, 2)
        );

        $this->assertSame(
            "{$sql_base} OFFSET 3",
            $this->dbFactory->addLimitOffset($sql_base, null, 3)
        );

        $this->assertSame(
            "{$sql_base} LIMIT 2 OFFSET 3",
            $this->dbFactory->addLimitOffset($sql_base, 2, 3)
        );
    }
}
