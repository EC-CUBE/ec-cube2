<?php

class SC_DB_DBFactory_MYSQLTest extends Common_TestCase
{
    /**
     * @var SC_DB_DBFactory_MYSQL
     */
    protected $dbFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbFactory = new SC_DB_DBFactory_MYSQL_Ex;
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
        $this->assertEquals(true, $this->dbFactory->isSkipDeleteIfNotExists());
    }
}
