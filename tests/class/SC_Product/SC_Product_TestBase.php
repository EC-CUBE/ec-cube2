<?php

$HOME = realpath(dirname(__FILE__)) . "/../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");
/**
 *
 */
class SC_Product_TestBase extends Common_TestCase
{
    /**
     * @var SC_Product_Ex
     */
    protected $objProducts;

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * DBに商品クラス情報を設定します.
     */
    protected function setUpProductClass()
    {
        $product_class = array(
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_class_id' => '1001',
                'product_id' => '1001',
                'product_type_id' => '1',
                'product_code' => 'code1001',
                'classcategory_id1' => '1001',
                'classcategory_id2' => '1002',
                'price01' => '1500',
                'price02' => '1500',
                'stock' => '99',
                'creator_id' => '1',
                'del_flg' => '0'
                  ),
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_class_id' => '1002',
                'product_id' => '1002',
                'product_type_id' => '2',
                'product_code' => 'code1002',
                'price02' => '2500',
                'creator_id' => '1',
                'stock_unlimited' => '1',
                'del_flg' => '0'
                  ),
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_class_id' => '2001',
                'product_id' => '2001',
                'product_type_id' => '1',
                'product_code' => 'code2001',
                'price02' => '2000',
                'creator_id' => '1',
                'stock_unlimited' => '1',
                'del_flg' => '1'
                  )
                               );

        $this->objQuery->delete('dtb_products_class');
        foreach ($product_class as $key => $item)
{
            $this->objQuery->insert('dtb_products_class', $item);
        }

        $this->setUpClass();
        $this->setUpClassCategory();
        $this->setUpProducts();
    }

    /**
     * DBに規格分類情報を登録します.
     */
    protected function setUpClass()
    {
        $table = 'dtb_class';

        $data = array(
            array(
                'class_id' => 1,
                'name' => '味',
                'rank' => 1,
                'creator_id' => 1,
                'update_date' => 'CURRENT_TIMESTAMP',
            ),
        );

        $this->objQuery->delete($table);
        foreach ($data as $item) {
            $this->objQuery->insert($table, $item);
        }
    }

    /**
     * DBに製品カテゴリ情報を登録します.
     */
    protected function setUpClassCategory()
    {
        $class_category = array(
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'classcategory_id' => '1001',
                'class_id' => '1',
                'creator_id' => '1',
                'name' => 'cat1001',
                'rank' => 1,
                  ),
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'classcategory_id' => '1002',
                'class_id' => '1',
                'creator_id' => '1',
                'name' => 'cat1002',
                'rank' => 2,
                  )
                                );

        // classcategory_id=0のものは削除しない
        $this->objQuery->delete('dtb_classcategory', 'classcategory_id <> 0');
        foreach ($class_category as $key => $item) {
            $this->objQuery->insert('dtb_classcategory', $item);
        }
    }

    /** 
     * DBに製品情報を登録します.
     */
    protected function setUpProducts()
    {
        $products = array(
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_id' => '1001',
                'name' => '製品名1001',
                'comment1' => 'コメント10011',
                'comment2' => 'コメント10012',
                'comment3' => 'コメント10013',
                'main_list_comment' => 'リストコメント1001',
                'main_comment' => 'メインコメント1001',
                'main_image' => '1001.jpg',
                'main_list_image' => '1001-main.jpg',
                'deliv_date_id' => '1',
                'del_flg' => '0',
                'creator_id' => '1',
                'status' => '1'
                  ),
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_id' => '1002',
                'name' => '製品名1002',
                'comment1' => 'コメント10021',
                'comment2' => 'コメント10022',
                'comment3' => 'コメント10023',
                'main_list_comment' => 'リストコメント1002',
                'main_image' => '1002.jpg',
                'main_list_image' => '1002-main.jpg',
                'deliv_date_id' => '2',
                'del_flg' => '0',
                'creator_id' => '1',
                'status' => '2'
                  ),
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_id' => '2001',
                'name' => '製品名2001',
                'comment1' => 'コメント20011',
                'comment2' => 'コメント20012',
                'comment3' => 'コメント20013',
                'main_list_comment' => 'リストコメント2001',
                'main_comment' => 'メインコメント2001',
                'main_image' => '2001.jpg',
                'main_list_image' => '2001-main.jpg',
                'deliv_date_id' => '1',
                'del_flg' => '1',
                'creator_id' => '1',
                'status' => '1'
                  )
                          );

        $this->objQuery->delete('dtb_products');
        foreach ($products as $key => $item) {
            $this->objQuery->insert('dtb_products', $item);
        }
    }
    
    /**
     * DBに商品ステータス情報を登録します.
     */
    protected function setUpProductStatus()
    {
        $class_category = array(
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_status_id' => '1',
                'product_id' => '1001',
                'creator_id' => '1',
                'del_flg' => '0'
                  ),
            array(
                'update_date' => 'CURRENT_TIMESTAMP',
                'product_status_id' => '1',
                'product_id' => '1002',
                'creator_id' => '1',
                'del_flg' => '0'
                  )
                                );

        $this->objQuery->delete('dtb_product_status');
        foreach ($class_category as $key => $item) {
            $this->objQuery->insert('dtb_product_status', $item);
        }
    }
}
