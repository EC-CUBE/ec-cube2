<?php

class SC_DB_DBFactory_PGSQLTest extends Common_TestCase
{
    /**
     * @var SC_DB_DBFactory_PGSQL
     */
    protected $dbFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbFactory = new SC_DB_DBFactory_PGSQL_Ex();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_DB_DBFactory_PGSQL_Ex', $this->dbFactory);
    }

    public function testGetTransactionIsolationLevel()
    {
        $this->assertEquals(SC_DB_DBFactory_Ex::ISOLATION_LEVEL_READ_COMMITTED, $this->dbFactory->getTransactionIsolationLevel());
    }

    public function testIsSkipDeleteIfNotExists()
    {
        $this->assertFalse($this->dbFactory->isSkipDeleteIfNotExists());
    }
}
