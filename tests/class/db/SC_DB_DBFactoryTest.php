<?php
/**
 * SC_DB_DBFactoryTest のテストクラス
 *
 * このクラスでは生成パターン (Factory) に関するテストを行う。
 * MDBS 機能面に対応したテストは、基本的なケース (即ち SC_DB_DBFactory に関するもの) を SC_DB_DBFactoryTestAbstract に記述し (継承によりこの Test クラスでも実行される)、MDBS 毎に差がある部分を SC_DB_DBFactory_*Test に記述する。
 *
 * @author Seasoft 塚田将久 (新規作成)
 */
class SC_DB_DBFactoryTest extends SC_DB_DBFactoryTestAbstract
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
        // インスタンスの取得が (DBMS 毎の) テストと異なるため、parent を使わない。(後述の TODO を実装する際には、parent を使うのが妥当かもしれない。)
        Common_TestCase::setUp();

        $this->dbFactoryPgsql = SC_DB_DBFactory_Ex::getInstance('pgsql');
        $this->dbFactoryMysql = SC_DB_DBFactory_Ex::getInstance('mysql');
        $this->dbFactoryMysqli = SC_DB_DBFactory_Ex::getInstance('mysqli');
        $this->dbFactory = SC_DB_DBFactory_Ex::getInstance('uknown');
        // TODO: SC_DB_DBFactory_Ex::getInstance() 引数なしのパターンを追加する、DB_TYPE に依存するテストとなる。
    }

    public function testGetInstancePgsql()
    {
        $this->assertInstanceOf('SC_DB_DBFactory', $this->dbFactoryPgsql);
        $this->assertInstanceOf('SC_DB_DBFactory_Ex', $this->dbFactoryPgsql);
        $this->assertInstanceOf('SC_DB_DBFactory_PGSQL', $this->dbFactoryPgsql);
        $this->assertInstanceOf('SC_DB_DBFactory_PGSQL_Ex', $this->dbFactoryPgsql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL', $this->dbFactoryPgsql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL_Ex', $this->dbFactoryPgsql);
    }

    public function testGetInstanceMysql()
    {
        $this->assertInstanceOf('SC_DB_DBFactory', $this->dbFactoryMysql);
        $this->assertInstanceOf('SC_DB_DBFactory_Ex', $this->dbFactoryMysql);
        $this->assertInstanceOf('SC_DB_DBFactory_MYSQL', $this->dbFactoryMysql);
        $this->assertInstanceOf('SC_DB_DBFactory_MYSQL_Ex', $this->dbFactoryMysql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL', $this->dbFactoryMysql);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL_Ex', $this->dbFactoryMysql);
    }

    public function testGetInstanceMysqli()
    {
        $this->assertSame(get_class($this->dbFactoryMysql), get_class($this->dbFactoryMysqli));
    }

    public function testGetInstanceUknown()
    {
        $this->assertInstanceOf('SC_DB_DBFactory', $this->dbFactory);
        $this->assertInstanceOf('SC_DB_DBFactory_Ex', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_MYSQL_Ex', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL', $this->dbFactory);
        $this->assertNotInstanceOf('SC_DB_DBFactory_PGSQL_Ex', $this->dbFactory);
    }
}
