<?php

class SC_DB_DBFactory_PGSQLTest extends SC_DB_DBFactoryTestAbstract
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
}
