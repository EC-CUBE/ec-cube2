<?php

class SC_DB_DBFactoryTest extends Common_TestCase
{
    /**
     * @var SC_DB_DBFactory_PGSQL
     */
    protected $dbFactoryPgsql;

    /**
     * @var SC_DB_DBFactory_MYSQL
     */
    protected $dbFactoryMysql;

    /**
     * @var SC_DB_DBFactory_MYSQL
     */
    protected $dbFactoryMysqli;

    /**
     * @var SC_DB_DBFactory
     */
    protected $dbFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbFactoryPgsql = SC_DB_DBFactory_Ex::getInstance('pgsql');
        $this->dbFactoryMysql = SC_DB_DBFactory_Ex::getInstance('mysql');
        $this->dbFactoryMysqli = SC_DB_DBFactory_Ex::getInstance('mysqli');
        $this->dbFactory = SC_DB_DBFactory_Ex::getInstance('uknown');
        // TODO: SC_DB_DBFactory_Ex::getInstance() 引数なしのパターンを追加する、DB_TYPE に依存するテストとなる。
    }

    public function testGetInstance_pgsql()
    {
        $this->assertInstanceOf('SC_DB_DBFactory', $this->dbFactoryPgsql);
        $this->assertInstanceOf('SC_DB_DBFactory_Ex', $this->dbFactoryPgsql);
        $this->assertInstanceOf('SC_DB_DBFactory_PGSQL', $this->dbFactoryPgsql);
        $this->assertInstanceOf('SC_DB_DBFactory_PGSQL_Ex', $this->dbFactoryPgsql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL', $this->dbFactoryPgsql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL_Ex', $this->dbFactoryPgsql);
    }

    public function testGetInstance_mysql()
    {
        $this->assertInstanceOf('SC_DB_DBFactory', $this->dbFactoryMysql);
        $this->assertInstanceOf('SC_DB_DBFactory_Ex', $this->dbFactoryMysql);
        $this->assertInstanceOf('SC_DB_DBFactory_MYSQL', $this->dbFactoryMysql);
        $this->assertInstanceOf('SC_DB_DBFactory_MYSQL_Ex', $this->dbFactoryMysql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL', $this->dbFactoryMysql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL_Ex', $this->dbFactoryMysql);
    }

    public function testGetInstance_mysqli()
    {
        $this->assertSame(get_class($this->dbFactoryMysql), get_class($this->dbFactoryMysqli));
    }

    public function testGetInstance_uknown()
    {
        $this->assertInstanceOf('SC_DB_DBFactory', $this->dbFactory);
        $this->assertInstanceOf('SC_DB_DBFactory_Ex', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL_Ex', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL_Ex', $this->dbFactory);
    }

    public function testGetTransactionIsolationLevel()
    {
        $this->assertEquals(SC_DB_DBFactory_Ex::ISOLATION_LEVEL_READ_COMMITTED, $this->dbFactory->getTransactionIsolationLevel());
    }

    public function testIsSkipDeleteIfNotExists()
    {
        $this->assertEquals(false, $this->dbFactory->isSkipDeleteIfNotExists());
    }
}
