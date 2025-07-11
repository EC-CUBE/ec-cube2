<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// {{{ requires

/**
 * SC_Query のテストケース.
 *
 * @author Kentaro Ohkouchi
 *
 * @version $Id$
 */
class SC_Query_Test extends PHPUnit_Framework_TestCase
{
    /**
     * MDB2 をグローバル変数のバックアップ対象から除外する。
     *
     * @var array
     *
     * @see PHPUnit_Framework_TestCase::$backupGlobals
     * @see PHPUnit_Framework_TestCase::$backupGlobalsExcludeList
     */
    protected $backupGlobalsExcludeList = [
        '_MDB2_databases',
        '_MDB2_dsninfo_default',
    ];

    /** @var SC_Query_Ex */
    protected $objQuery;

    protected $expected;
    protected $actual;

    protected function setUp(): void
    {
        $this->objQuery = SC_Query_Ex::getSingletonInstance();
        $this->objQuery->begin();
    }

    protected function tearDown(): void
    {
        // MySQL では CREATE TABLE がロールバックされないので DROP TABLE を行う
        $this->dropTestTable();
        $this->objQuery->rollback();
        $this->objQuery = null;
    }

    protected function verify()
    {
        $this->assertEquals($this->expected, $this->actual);
    }

    /**
     * インスタンスを取得するテストケース.
     */
    public function testGetInstance()
    {
        $this->expected = true;
        $this->actual = is_object($this->objQuery);

        $this->verify();
    }

    /**
     * SC_Query::query() を使用して, CREATE TABLE を実行するテストケース.
     */
    public function testCreateTable()
    {
        $result = $this->createTestTable();

        $this->expected = false;
        $this->actual = PEAR::isError($result);

        $this->verify();
    }

    /**
     * SC_Query::getAll() のテストケース.
     */
    public function testGetAll()
    {
        $result = $this->createTestTable();
        $result = $this->setTestData(1, '2', 'f');

        $this->expected = [['id' => '1',
            'column1' => '1',
            'column2' => '2',
            'column3' => 'f', ]];
        $this->actual = $this->objQuery->getAll('SELECT * FROM test_table WHERE id = ?', [1]);

        $this->verify();
    }

    /**
     * SC_Query::select() のテストケース.
     */
    public function testSelect()
    {
        $this->createTestTable();
        $result = $this->setTestData(1, '2', 'f');

        $this->expected = [['id' => '1',
            'column1' => '1',
            'column2' => '2',
            'column3' => 'f', ]];

        $this->actual = $this->objQuery->setWhere('id = ?')
                                       ->setOrder('id')
                                       ->select('*', 'test_table', '', [1]);

        $this->verify();
    }

    /**
     * SC_Query::getOne() のテストケース.
     */
    public function testGetOne()
    {
        $this->createTestTable();
        $this->setTestData(1, '2', 'f');
        $this->setTestData(1, '2', 'f');
        $this->setTestData(1, '2', 'f');

        $this->expected = 3;
        $this->actual = $this->objQuery->getOne('SELECT COUNT(*) FROM test_table');

        $this->verify();
    }

    /**
     * SC_Query::getRow() のテストケース.
     */
    public function testGetRow()
    {
        $this->createTestTable();
        $this->setTestData(1, '1', 'f');
        $this->setTestData(2, '2', 'f');
        $this->setTestData(3, '3', 'f');

        $this->expected = ['column1' => 1, 'column2' => 1];
        $this->actual = $this->objQuery->getRow('column1, column2', 'test_table', 'id = ?', [1]);
        $this->verify();
    }

    /**
     * SC_Query::getCol() のテストケース.
     */
    public function testGetCol()
    {
        $this->createTestTable();
        $this->setTestData(1, '1', 'f');
        $this->setTestData(2, '2', 'f');
        $this->setTestData(3, '3', 'f');

        $this->expected = [1, 2];
        $this->actual = $this->objQuery->getCol('column1', 'test_table', 'id < ?', [3]);

        $this->verify();
    }

    /**
     * SC_Query::query() で INSERT を実行するテストケース.
     */
    public function testQuery1()
    {
        $this->createTestTable();
        $sql = 'INSERT INTO test_table VALUES (?, ?, ?, ?)';
        $data = ['1', '1', '1', 'f'];

        $this->objQuery->query($sql, $data);

        $this->expected = [['id' => '1',
            'column1' => '1',
            'column2' => '1',
            'column3' => 'f', ]];

        $this->actual = $this->objQuery->getAll('SELECT * FROM test_table');

        $this->verify();
    }

    public function testInsert()
    {
        $this->createTestTable();

        $this->objQuery->insert(
            'test_table',
            ['id' => '1',
                'column1' => '1',
                'column2' => '1',
                'column3' => 'f', ]
        );

        $this->expected = [['id' => '1',
            'column1' => '1',
            'column2' => '1',
            'column3' => 'f', ]];

        $this->actual = $this->objQuery->getAll('SELECT * FROM test_table');

        $this->verify();
    }

    /**
     * SC_Query::query() で UPDATE を実行するテストケース.
     */
    public function testQuery2()
    {
        $this->createTestTable();
        $this->setTestData(1, '2', 'f');

        $sql = 'UPDATE test_table SET column1 = ?, column2 = ? WHERE id = ?';
        $data = ['2', '2', '1'];

        $this->objQuery->query($sql, $data);

        $this->expected = [['id' => '1',
            'column1' => '2',
            'column2' => '2',
            'column3' => 'f', ]];

        $this->actual = $this->objQuery->getAll('SELECT * FROM test_table');

        $this->verify();
    }

    public function testUpdate()
    {
        $this->createTestTable();
        $this->setTestData(1, '2', 'f');

        $this->objQuery->update(
            'test_table',
            [
                'id' => '1',
                'column1' => '2',
                'column2' => '2',
                'column3' => 'f',
            ],
            'id = ?',
            [1]
        );
        $this->expected = [
            [
                'id' => '1',
                'column1' => '2',
                'column2' => '2',
                'column3' => 'f',
            ],
        ];

        $this->actual = $this->objQuery->getAll('SELECT * FROM test_table');

        $this->verify();
    }

    public function testListTables()
    {
        $this->objQuery->setOrder('');
        $tables = $this->objQuery->listTables();
        $this->assertTrue(in_array('mtb_zip', $tables));
    }

    public function testListSequences()
    {
        $sequences = $this->objQuery->listSequences();
        $this->assertTrue(in_array('dtb_products_product_id', $sequences));
    }

    public function testListTableFields()
    {
        $this->expected = ['id', 'name', 'rank', 'remarks'];
        $this->actual = $this->objQuery->listTableFields('mtb_constants');
        $this->verify();
    }

    public function testListTableIndexes()
    {
        $indexes = $this->objQuery->listTableIndexes('dtb_order_detail');
        $this->assertTrue(in_array('dtb_order_detail_product_id_key', $indexes));
    }

    protected function createTestTable()
    {
        $sql = 'CREATE TABLE test_table ('
            .'id SERIAL PRIMARY KEY,'
            .'column1 numeric(9),'
            .'column2 varchar(20),'
            .'column3 char(1)'
            .')';

        return $this->objQuery->query($sql);
    }

    protected function dropTestTable()
    {
        $this->objQuery->setOrder('');
        $tables = $this->objQuery->listTables();
        if (in_array('test_table', $tables)) {
            $this->objQuery->query('DROP TABLE test_table');
        }

        return;
    }

    protected function setTestData($column1 = null, $column2 = null, $column3 = null)
    {
        $fields_values = [$column1, $column2, $column3];
        $sql = 'INSERT INTO test_table (column1, column2, column3) VALUES (?, ?, ?)';
        $result = $this->objQuery->query($sql, $fields_values);
        if (PEAR::isError($result)) {
            throw new \Exception($result->getMessage());
        }

        return $result;
    }

    /**
     * SC_Query::setLimit() のテストケース.
     */
    public function testSetLimit()
    {
        $this->createTestTable();
        $this->setTestData(1);
        $this->setTestData(2);
        $this->setTestData(3);

        $this->expected = [1, 2];
        $this->actual = $this->objQuery
            ->setOrder('column1')
            ->setLimit(2)
            ->getCol('column1', 'test_table')
        ;
        $this->verify();
    }

    /**
     * SC_Query::setLimitOffset() のテストケース.
     */
    public function testSetLimitOffset()
    {
        $this->createTestTable();
        $this->setTestData(1);
        $this->setTestData(2);
        $this->setTestData(3);
        $this->setTestData(4);

        $this->expected = [2, 3];
        $this->actual = $this->objQuery
            ->setOrder('column1')
            ->setLimitOffset(2, 1)
            ->getCol('column1', 'test_table')
        ;
        $this->verify();
    }

    /**
     * SC_Query::setLimit() SC_Query::setOffset() 併用のテストケース.
     */
    public function testSetLimitAndSetOffset()
    {
        $this->createTestTable();
        $this->setTestData(1);
        $this->setTestData(2);
        $this->setTestData(3);
        $this->setTestData(4);

        $this->expected = [2, 3];
        $this->actual = $this->objQuery
            ->setOrder('column1')
            ->setLimit(2)
            ->setOffset(1)
            ->getCol('column1', 'test_table')
        ;
        $this->verify();
    }

    /**
     * SC_Query::setOffset() のテストケース.
     */
    public function testSetOffset()
    {
        $this->createTestTable();
        $this->setTestData(1);
        $this->setTestData(2);
        $this->setTestData(3);

        $this->expected = [2, 3];
        $this->actual = $this->objQuery
            ->setOrder('column1')
            ->setOffset(1)
            ->getCol('column1', 'test_table')
        ;
        $this->verify();
    }

    /**
     * SC_Query::getSql() 経由で SC_Query::resetAdditionalClauses() が呼ばれていることを確認する。
     */
    public function testResetAdditionalClausesViaGetSql()
    {
        $this->objQuery
            ->setGroupBy('column1')
            ->setOrder('column1')
            ->setLimit(2)
            ->setOffset(1)
        ;

        // 最初はプロパティを確認する。
        // getSql() 実行前
        $this->assertNotEmpty($this->objQuery->groupby);
        $this->assertNotEmpty($this->objQuery->order);
        $this->assertNotNull($this->objQuery->limit);
        $this->assertNotNull($this->objQuery->offset);

        $sql1 = $this->objQuery->getSql('*');

        // getSql() 実行後
        $this->assertEmpty($this->objQuery->groupby);
        $this->assertEmpty($this->objQuery->order);
        $this->assertNull($this->objQuery->limit);
        $this->assertNull($this->objQuery->offset);

        $sql2 = $this->objQuery->getSql('*');

        // SQL を確認する。
        // getSql() 実行前
        $this->assertStringContainsString(' GROUP BY ', $sql1);
        $this->assertStringContainsString(' ORDER BY ', $sql1);
        $this->assertStringContainsString(' LIMIT ', $sql1);
        $this->assertStringContainsString(' OFFSET ', $sql1);

        // getSql() 実行後
        $this->assertStringNotContainsString(' GROUP BY ', $sql2);
        $this->assertStringNotContainsString(' ORDER BY ', $sql2);
        $this->assertStringNotContainsString(' LIMIT ', $sql2);
        $this->assertStringNotContainsString(' OFFSET ', $sql2);
    }
}
