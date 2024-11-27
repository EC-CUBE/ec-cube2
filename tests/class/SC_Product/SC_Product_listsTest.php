<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_listsTest extends SC_Product_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductClass();
        $this->objProducts = new SC_Product_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testlists商品一覧取得()
    {
        // 更新日を取得
        $col = 'update_date';
        $table = 'dtb_products';
        $where = 'product_id IN (1001, 1002)';
        $this->objQuery->setOrder('product_id');
        $arrRet = $this->objQuery->getCol($col, $table, $where);

        $this->expected = [
            0 => [
                'product_id' => '1001',
                'product_code_min' => 'code1001',
                'product_code_max' => 'code1001',
                'name' => '製品名1001',
                'comment1' => 'コメント10011',
                'comment2' => 'コメント10012',
                'comment3' => 'コメント10013',
                'main_list_comment' => 'リストコメント1001',
                'main_image' => '1001.jpg',
                'main_list_image' => '1001-main.jpg',
                'price01_min' => '1500',
                'price01_max' => '1500',
                'price02_min' => '1500',
                'price02_max' => '1500',
                'stock_min' => '99',
                'stock_max' => '99',
                'stock_unlimited_min' => '0',
                'stock_unlimited_max' => '0',
                'deliv_date_id' => '1',
                'status' => '1',
                'del_flg' => '0',
                'update_date' => $arrRet[0],
            ],
            1 => [
                'product_id' => '1002',
                'product_code_min' => 'code1002',
                'product_code_max' => 'code1002',
                'name' => '製品名1002',
                'comment1' => 'コメント10021',
                'comment2' => 'コメント10022',
                'comment3' => 'コメント10023',
                'main_list_comment' => 'リストコメント1002',
                'main_image' => '1002.jpg',
                'main_list_image' => '1002-main.jpg',
                'price01_min' => null,
                'price01_max' => null,
                'price02_min' => '2500',
                'price02_max' => '2500',
                'stock_min' => null,
                'stock_max' => null,
                'stock_unlimited_min' => '1',
                'stock_unlimited_max' => '1',
                'deliv_date_id' => '2',
                'status' => '2',
                'del_flg' => '0',
                'update_date' => $arrRet[1],
            ],
        ];

        // SC_Product::lists() の第二引数を使用するケースは
        // SC_DB_DBFactory::alldtlSQL() が利用するエイリアスである alldtl.product_id に対応する WHERE 句が必要
        $result = $this->objProducts->lists($this->objQuery, [1001, 1002]);
        $this->assertNull($result);

        $this->objQuery->setWhere('product_id IN (?, ?)', [1001, 1002]);
        $this->actual = $this->objProducts->lists($this->objQuery);

        $this->verify('商品一覧');
    }
}
