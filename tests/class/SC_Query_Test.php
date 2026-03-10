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
     * dtb_products_class の listTableFields で数値型カラムが返されることを確認する.
     *
     * SQLite3 では DOUBLE 型のカラムが MDB2 の _getTableColumns で
     * 正しくパースされない場合、カラムが欠落する問題があった.
     *
     * @see https://github.com/EC-CUBE/ec-cube2/pull/1318
     */
    public function testListTableFieldsProductsClass()
    {
        $fields = $this->objQuery->listTableFields('dtb_products_class');

        $numericCols = ['stock', 'sale_limit', 'price01', 'price02', 'deliv_fee', 'point_rate'];
        foreach ($numericCols as $col) {
            $this->assertContains($col, $fields, "dtb_products_class に {$col} カラムが存在すること");
        }

        $this->assertCount(19, $fields, 'dtb_products_class のカラム数');
    }

    /**
     * dtb_products_class の extractOnlyColsOf で数値型カラムが除外されないことを確認する.
     *
     * @see https://github.com/EC-CUBE/ec-cube2/pull/1318
     */
    public function testExtractOnlyColsOfProductsClass()
    {
        $params = [
            'product_class_id' => 99999,
            'product_id' => 99999,
            'classcategory_id1' => 0,
            'classcategory_id2' => 0,
            'product_code' => 'TEST_CODE',
            'product_type_id' => 1,
            'stock_unlimited' => 0,
            'stock' => 50,
            'price01' => 2000,
            'price02' => 1800,
            'point_rate' => 10,
            'creator_id' => 2,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'del_flg' => 0,
            'nonexistent_col' => 'should_be_removed',
        ];

        $result = $this->objQuery->extractOnlyColsOf('dtb_products_class', $params);

        $this->assertArrayHasKey('stock', $result);
        $this->assertArrayHasKey('price01', $result);
        $this->assertArrayHasKey('price02', $result);
        $this->assertArrayHasKey('point_rate', $result);
        $this->assertArrayNotHasKey('nonexistent_col', $result);
        $this->assertCount(count($params) - 1, $result);
    }

    /**
     * 数値型カラムを持つ主要テーブルで listTableFields が正しくカラムを返すことを確認する.
     *
     * @dataProvider numericColumnTablesProvider
     *
     * @see https://github.com/EC-CUBE/ec-cube2/pull/1318
     */
    public function testListTableFieldsNumericColumns($table, $expectedNumericCols)
    {
        $fields = $this->objQuery->listTableFields($table);

        foreach ($expectedNumericCols as $col) {
            $this->assertContains($col, $fields, "{$table} に {$col} カラムが存在すること");
        }
    }

    public static function numericColumnTablesProvider()
    {
        return [
            'dtb_baseinfo' => [
                'dtb_baseinfo',
                ['free_rule', 'point_rate', 'welcome_point', 'downloadable_days'],
            ],
            'dtb_order' => [
                'dtb_order',
                ['subtotal', 'discount', 'deliv_fee', 'charge', 'use_point', 'add_point', 'tax', 'total', 'payment_total'],
            ],
            'dtb_customer' => [
                'dtb_customer',
                ['buy_times', 'buy_total', 'point'],
            ],
            'dtb_order_detail' => [
                'dtb_order_detail',
                ['price', 'quantity', 'point_rate', 'tax_rate'],
            ],
            'dtb_tax_rule' => [
                'dtb_tax_rule',
                ['tax_rate', 'tax_adjust'],
            ],
        ];
    }

    /**
     * extractOnlyColsOf が dtb_order の数値型カラムを保持することを確認する.
     *
     * fixture generator が使用する extractOnlyColsOf の挙動を検証する.
     *
     * @see https://github.com/EC-CUBE/ec-cube2/pull/1318
     */
    public function testExtractOnlyColsOfOrder()
    {
        $params = [
            'order_id' => 99999,
            'customer_id' => 1,
            'subtotal' => 3000,
            'discount' => 0,
            'deliv_fee' => 500,
            'charge' => 300,
            'use_point' => 0,
            'add_point' => 30,
            'tax' => 300,
            'total' => 3800,
            'payment_total' => 3800,
            'status' => 1,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'del_flg' => 0,
            'nonexistent_col' => 'should_be_removed',
        ];

        $result = $this->objQuery->extractOnlyColsOf('dtb_order', $params);

        $numericCols = ['subtotal', 'discount', 'deliv_fee', 'charge', 'use_point', 'add_point', 'tax', 'total', 'payment_total'];
        foreach ($numericCols as $col) {
            $this->assertArrayHasKey($col, $result, "dtb_order の {$col} が保持されること");
        }
        $this->assertArrayNotHasKey('nonexistent_col', $result);
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
